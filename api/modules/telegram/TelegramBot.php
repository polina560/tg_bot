<?php

namespace api\modules\telegram;

use common\components\exceptions\ModelSaveException;
use common\models\DialogState;
use common\models\TelegramMessage;
use common\models\TelegramMessageImage;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Module;

class TelegramBot extends Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\telegram\controllers';

    /**
     * @param TelegramMessage        $text
     * @param TelegramMessageImage[] $images
     */
    static public function imageToArray(TelegramMessage $text, int $price = 0, int $reminder = 0)
    {
        if ($images = TelegramMessageImage::find()->where(['telegram_message_id' => $text->id])->orderBy(
            'serial_number'
        )->all()) {
            foreach ($images as $index => $image) {
                if ($index == 0) {
                    $media_group[] = new InputMediaPhoto(
                        [
                            'media' => Yii::getAlias('@htdocs') . $image->image,
                            'caption' => sprintf($text->text, $price, $reminder)
                        ]
                    );
                } else {
                    $media_group[] = new InputMediaPhoto(['media' => Yii::getAlias('@htdocs') . $image->image]);
                }
            }
            return $media_group;
        } else {
            return [];
        }
    }

    /**
     * @throws TelegramException
     */
    static public function sendNotification(): void
    {
        $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);
        $dialogs = DialogState::find()->all();

        foreach ($dialogs as $dialog) {
            if ($dialog->last_msg_time < time() - 60 && $dialog->quantity_reminder_msg == 0) {
                $result = Request::sendMessage([
                    'chat_id' => $dialog->chat_id,
                    'text' => TelegramMessage::find()->where(['key' => '/notification'])->andWhere(['serial_number' => 1])->one()->text,
                ]);
                $dialog->quantity_reminder_msg++;
                $dialog->last_msg_time = time();
                if (!$dialog->save()) {
                    throw new ModelSaveException($dialog);
                }
//                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result, true));

                continue;
            }
            if ($dialog->last_msg_time < time() - 3 * 60 && $dialog->quantity_reminder_msg == 1) {
                $result =
                    Request::sendMessage([
                        'chat_id' => $dialog->chat_id,
                        'text' => TelegramMessage::find()->where(['key' => '/notification'])->andWhere(['serial_number' => 2])->one()->text,
                    ]);
                $dialog->quantity_reminder_msg++;
                $dialog->last_msg_time = time();
                if (!$dialog->save()) {
                    throw new ModelSaveException($dialog);
                }
//                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result, true));

                continue;
            }
            if ($dialog->last_msg_time < time() - 5 * 60 && $dialog->quantity_reminder_msg == 2) {
                $result = Request::sendMessage([
                    'chat_id' => $dialog->chat_id,
                    'text' => TelegramMessage::find()->where(['key' => '/notification'])->andWhere(['serial_number' => 3])->one()->text,
                ]);
                $dialog->quantity_reminder_msg++;
                $dialog->last_msg_time = time();
                if (!$dialog->save()) {
                    throw new ModelSaveException($dialog);
                }
//                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result, true));
            }
        }
    }

    static function updateLastMessageTime($user_id, $chat_id): string
    {
        if ($dialog = DialogState::findOne(['user_id' => $user_id])) {
            $dialog->last_msg_time = time();
            $dialog->quantity_reminder_msg = 0;
            if (!$dialog->save()) {
                throw new ModelSaveException($dialog);
            }
            return 'update';
        } else {
            $dialog = new DialogState();
            $dialog->user_id = $user_id;
            $dialog->chat_id = $chat_id;
            $dialog->last_msg_time = time();
            $dialog->last_msg_id = 1;
            $dialog->remainder = 5000; //TODO: занести значения в БД
            if (!$dialog->save()) {
                throw new ModelSaveException($dialog);
            }
            return 'new';
        }
    }
}

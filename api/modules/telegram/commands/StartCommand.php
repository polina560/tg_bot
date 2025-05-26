<?php

namespace api\modules\telegram\commands;

use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use common\models\TelegramMessageImage;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;
use PhpTelegramBot\FluentKeyboard\InlineKeyboard\InlineKeyboardButton;
use PhpTelegramBot\FluentKeyboard\InlineKeyboard\InlineKeyboardMarkup;
use PhpTelegramBot\FluentKeyboard\ReplyKeyboard\KeyboardButton;
use PhpTelegramBot\FluentKeyboard\ReplyKeyboard\ReplyKeyboardMarkup;
use Yii;

class StartCommand extends UserCommand
{

    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getFrom()->getId();
        $bot_username = Yii::$app->environment->BOT_USERNAME;
        $user_id = $message->getFrom()->getId();

        $member = Request::getChatMember(['chat_id' => $chat_id, 'user_id' => $user_id])->toJson();
        $member = json_decode($member, true);
        $status = $member['result']['status'];
        $member_statuses = ['creator', 'administrator', 'member'];

        file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($status, true));

        //проверка подписки на канал
        if (!in_array($status, $member_statuses)) {
            $text = TelegramMessage::find()->where(['key' => 'notMember'])->one();
            if ($media_group = $this->imageToArray($text)) {
                $result = Request::sendMediaGroup([
                    'chat_id' => $bot_username,
                    'media' => $media_group,
                    'reply_markup' => new InlineKeyboard([
                        ['text' => 'Подписался', 'callback_data' => 'is-member']
                    ])
                ]);

                return $result;
            }
        } else {
            $text = TelegramMessage::find()->where(['key' => $this->usage])->one();

            $media_group = $this->imageToArray($text);
            Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group,
            ]);

            $result = Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Получить 5000 монет',
                'reply_markup' => new InlineKeyboard([
                    ['text' => 'Получить монеты', 'callback_data' => 'get-money']
                ])
            ]);
            return $result;
        }
        return $this->replyToChat($text->text);
    }

    /**
     * @param TelegramMessage        $text
     * @param TelegramMessageImage[] $images
     */
    private function imageToArray(TelegramMessage $text)
    {
        if ($images = TelegramMessageImage::find()->where(['telegram_message_id' => $text->id])->all()) {
            foreach ($images as $index => $image) {
                if ($index == 0) {
                    $media_group[] = new InputMediaPhoto(
                        ['media' => Yii::getAlias('@htdocs') . $image->image, 'caption' => $text->text]
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

}

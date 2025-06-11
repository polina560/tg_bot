<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use Codeception\Exception\ModuleException;
use common\components\exceptions\ModelSaveException;
use common\models\DialogState;
use common\models\TelegramButton;
use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Yii;

class CallbackqueryCommand extends SystemCommand
{

    protected $name = 'callbackquery';
    protected $description = 'Handle the callback query';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $callback_query = $this->getCallbackQuery();
        $callback_data = $callback_query->getData();
        $user_id = $callback_query->getFrom()->getId();
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();

        switch ($callback_data) {
            case 'get-money':
                return $this->handleAnswerGetMoney($chat_id, $user_id);
            case 'is-member':
                return $this->handleActionIsMember($chat_id, $user_id);
            case 'play_1':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 1);
            case 'play_2':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 2);
            case 'play_3':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 3);
            case 'play_4':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 4);
            case 'play_5':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 5);
            default:
                return Request::answerCallbackQuery([
                    'callback_query_id' => $callback_query->getId(),
                    'text' => 'Неизвестная команда',
                    'show_alert' => false,
                ]);
        }
    }

    protected function handleAnswerGetMoney($chat_id, $user_id): ServerResponse
    {
        try {
            $text = TelegramMessage::find()
                ->where(['key' => 'play'])
                ->andWhere(['serial_number' => 1])
                ->one();
            if (!$text) {
                throw new \Exception('Сообщение не найдено');
            }

            $media_group = TelegramBot::imageToArray($text);
            $mediaResponse = Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group
            ]);

            if (!$mediaResponse->isOk()) {
                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($media_group, true));
                throw new \Exception('Ошибка отправки медиа: ' . $mediaResponse->getDescription());
            }

            $inline_keyboard = new InlineKeyboard([
                ['text' => '1', 'callback_data' => 'play_1'],
                ['text' => '2', 'callback_data' => 'play_2'],
                ['text' => '3', 'callback_data' => 'play_3'],
                ['text' => '4', 'callback_data' => 'play_4'],
                ['text' => '5', 'callback_data' => 'play_5'],
            ]);

            $messageResponse = Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Выберите любой вариант',
                'reply_markup' => $inline_keyboard
            ]);

            $dialog = DialogState::find()->where(['user_id' => $user_id])->one();
            if (!empty($dialog)) {
                $dialog->delete();
            }
            $dialog = new DialogState();
            $dialog->user_id = $user_id;
            $dialog->chat_id = $chat_id;
            $dialog->last_msg_time = time();
            $dialog->last_msg_id = 1;
            $dialog->remainder = 5000; //TODO: занести значения в БД

            if (!$dialog->save()) {
                file_put_contents(
                    Yii::getAlias('@htdocs/uploads') . '/message.txt',
                    print_r(new ModelSaveException($dialog), true)
                );
                new ModelSaveException($dialog);
            }

            Request::answerCallbackQuery([
                'text' => 'Монеты будут зачислены скоро!',
                'show_alert' => true,
            ]);

            return $messageResponse;
        } catch (\Exception $e) {
            error_log('Error in handleAnswerGetMoney: ' . $e->getMessage());
            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/error.txt', print_r($e->getMessage(), true));

            // Обязательно отвечаем на callback даже при ошибке
            Request::answerCallbackQuery([
                'text' => 'Произошла ошибка: ' . $e->getMessage(),
                'show_alert' => true,
            ]);

            return Request::sendMessage([
                'chat_id' => $chat_id . $e->getMessage(),
                'text' => 'Произошла ошибка, попробуйте позже'
            ]);
        }
    }

    protected function handleActionIsMember($chat_id, $user_id): ServerResponse
    {
        $member = Request::getChatMember(['chat_id' => $chat_id, 'user_id' => $user_id])->toJson();
        $member = json_decode($member, true);
        $status = $member['result']['status'];
        $member_statuses = ['creator', 'administrator', 'member'];

        if (!in_array($status, $member_statuses)) {
            $text = TelegramMessage::find()->where(['key' => 'notMember'])->one();
            if ($media_group = TelegramBot::imageToArray($text)) {
                $result = Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $text->text,
                    'reply_markup' => new InlineKeyboard([
                        ['text' => 'Подписался', 'callback_data' => 'is-member']
                    ])
                ]);

                return $result;
            }
        }

        return $this->handleAnswerGetMoney($chat_id, $user_id);
    }

    protected function handleActionPlay($chat_id, $user_id, $message_id, int $number): ServerResponse
    {
        //проверка на конец теста
        $dialog = DialogState::find()->where(['user_id' => $user_id])->one();
        if ($dialog->last_msg_id >= 5) {
            Request::deleteMessage([
                'chat_id' => $chat_id,
                'message_id' => $message_id
            ]);
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'конец'
            ]);
        }

        //сообщения
        $last_text = TelegramMessage::find()->where(['key' => 'play'])->andWhere(['serial_number' => $dialog->last_msg_id]
        )->one();
        //кнопки сообщения
        $button = TelegramMessageButton::find()->where(['telegram_message_id' => $last_text->id])->andWhere(['serial_number' => $number])->one();
        if (!$button) {
            throw new \Exception('Сообщение не найдено');
        }

        $price = $button->value;
        if ($price <= $dialog->remainder) {
            $remainder = $dialog->remainder - $price;
            switch ($number) {
                case 1:
                    $dialog->ans_1 += 1;
                    break;
                case 2:
                    $dialog->ans_2 += 1;
                    break;
                case 3:
                    $dialog->ans_3 += 1;
                    break;
                case 4:
                    $dialog->ans_4 += 1;
                    break;
                case 5:
                    $dialog->ans_5 += 1;
                    break;
                default:
                    break;
            }
            $dialog->remainder = $remainder;
            $dialog->last_msg_id += 1;
            $dialog->last_msg_time = time();
            if (!$dialog->save()) {
                throw new ModelSaveException($dialog);
            }
        } else {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'нет монет'
            ]);
        }


        Request::deleteMessage([
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]);


        //сообщения
        $text = TelegramMessage::find()->where(['key' => 'play'])->andWhere(['serial_number' => $dialog->last_msg_id + 1]
        )->one();
        if (!$text) {
            throw new \Exception('Сообщение не найдено');
        }
        if ($media_group = TelegramBot::imageToArray($text, $price, $remainder)) {
           Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group
            ]);
        } else {
            Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => sprintf($text->text, $price, $remainder)
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите любой вариант' . '(' . $number . ')',
            'reply_markup' => self::keyboard()
        ]);
    }

    static function keyboard()
    {
        return new InlineKeyboard([
            ['text' => '1', 'callback_data' => 'play_1'],
            ['text' => '2', 'callback_data' => 'play_2'],
            ['text' => '3', 'callback_data' => 'play_3'],
            ['text' => '4', 'callback_data' => 'play_4'],
            ['text' => '5', 'callback_data' => 'play_5'],
        ]);
    }
}

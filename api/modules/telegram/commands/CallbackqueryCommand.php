<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use common\models\TelegramMessage;
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
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();

        switch ($callback_data) {
            case 'get-money':
                return $this->handleAnswerGetMoney($chat_id);
            case 'is-member':
                return $this->handleActionIsMember($chat_id);
            default:
                return Request::answerCallbackQuery([
                    'callback_query_id' => $callback_query->getId(),
                    'text' => 'Неизвестная команда',
                    'show_alert' => false,
                ]);
        }
    }

    protected function handleAnswerGetMoney($chat_id): ServerResponse
    {
        try {
            // 1. Получаем данные сообщения
            $text = TelegramMessage::find()
                ->where(['key' => 'play'])
                ->andWhere(['serial_number' => 1])
                ->one();

            if (!$text) {
                file_put_contents(
                    Yii::getAlias('@htdocs/uploads') . '/message.txt',
                    print_r('Сообщение не найдено', true)
                );
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


//             3. Создаем и отправляем сообщение с кнопками
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

            // 4. Отвечаем на callback запрос
            Request::answerCallbackQuery([
                'text' => 'Монеты будут зачислены скоро!',
                'show_alert' => true,
            ]);

            return $messageResponse;
        } catch (\Exception $e) {
            error_log('Error in handleAnswerGetMoney: ' . $e->getMessage());

            // Обязательно отвечаем на callback даже при ошибке
            Request::answerCallbackQuery([
                'text' => 'Произошла ошибка: ' . $e->getMessage(),
                'show_alert' => true,
            ]);

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Произошла ошибка, попробуйте позже'
            ]);
        }
    }

    protected function handleActionIsMember($chat_id): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getFrom()->getId();
        $bot_username = Yii::$app->environment->BOT_USERNAME;
        $user_id = $message->getFrom()->getId();

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
                        ['text' => 'Получить монеты', 'callback_data' => 'get-money']
                    ])
                ]);

                return $result;
            }
        }
        return $this->handleAnswerGetMoney($chat_id);

    }
}

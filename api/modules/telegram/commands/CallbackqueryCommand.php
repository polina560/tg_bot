<?php

namespace api\modules\telegram\commands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

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
                return $this->handleAnswerA($chat_id, $message_id);
            case 'action_2':
                return $this->handleAction2($chat_id);
            default:
                return Request::answerCallbackQuery([
                    'callback_query_id' => $callback_query->getId(),
                    'text' => 'Неизвестная команда',
                    'show_alert' => false,
                ]);
        }
    }

    protected function handleAnswerA($chat_id, $message_id): ServerResponse
    {
        Request::sendMessage([
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => 'Вы выбрали "Получить монеты"!',
        ]);

        // Отправляем уведомление о нажатии
        return Request::answerCallbackQuery([

            'text' => 'Монеты будут зачислены скоро!',
            'show_alert' => true,
        ]);
    }

    protected function handleAction2($chat_id): ServerResponse
    {
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Вы выбрали вторую кнопку!',
        ]);
    }
}

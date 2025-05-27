<?php

namespace api\modules\telegram\commands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class ShareCommand extends UserCommand
{

    protected $name = 'share';
    protected $description = 'Share command';
    protected $usage = '/share';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

//        $command = trim($message->getText(true)); // Получает текст после команды

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите чат, в котором хотите поделиться ботом:',
            'reply_markup' => new InlineKeyboard([
                ['text' => 'Поделиться', 'switch_inline_query' => 'telegram bot'],
            ])
        ]);
    }
}

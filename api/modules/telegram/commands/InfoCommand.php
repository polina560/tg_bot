<?php

namespace api\modules\telegram\commands;

use common\models\TelegramMessage;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;
use PhpTelegramBot\FluentKeyboard\InlineKeyboard\InlineKeyboardButton;
use PhpTelegramBot\FluentKeyboard\InlineKeyboard\InlineKeyboardMarkup;
use PhpTelegramBot\FluentKeyboard\ReplyKeyboard\KeyboardButton;
use PhpTelegramBot\FluentKeyboard\ReplyKeyboard\ReplyKeyboardMarkup;

class InfoCommand extends UserCommand
{
    protected $name = 'info';
    protected $description = 'Info command';
    protected $usage = '/info';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

//        $command = trim($message->getText(true)); // Получает текст после команды

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите действие:',
            'reply_markup' => new InlineKeyboard([
                ['text' => 'Официальный сайт', 'url' => 'https://example.com'],
            ])
        ]);
    }
}

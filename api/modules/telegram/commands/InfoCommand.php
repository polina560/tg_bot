<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use common\components\exceptions\ModelSaveException;
use common\models\TelegramMessage;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
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

    /**
     * @throws ModelSaveException
     * @throws TelegramException
     */
    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        TelegramBot::updateLastMessageTime($user_id, $chat_id);

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите действие:',
            'reply_markup' => new InlineKeyboard([
                ['text' => 'Официальный сайт', 'url' => 'https://example.com'],
            ])
        ]);
    }
}

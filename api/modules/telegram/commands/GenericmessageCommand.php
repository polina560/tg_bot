<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use common\components\exceptions\ModelSaveException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Exception\TelegramException;

class GenericmessageCommand extends UserCommand
{

    protected $name = 'genericmessage';
    protected $description = 'Handle generic messages';

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

        $text = $message->getText();

        return $this->replyToChat("Вы написали: $text");
    }
}

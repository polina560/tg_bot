<?php

namespace api\modules\telegram\commands;

use Longman\TelegramBot\Commands\UserCommand;

class GenericmessageCommand extends UserCommand
{

    protected $name = 'genericmessage';
    protected $description = 'Handle generic messages';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $message = $this->getMessage();
        $text = $message->getText();

        return $this->replyToChat("Вы написали: $text");
    }
}

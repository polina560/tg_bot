<?php

namespace api\modules\telegram\commands;

use Longman\TelegramBot\Commands\UserCommand;

class StartCommand extends UserCommand
{

    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        return $this->replyToChat("Привет! Я бот!");
    }
}

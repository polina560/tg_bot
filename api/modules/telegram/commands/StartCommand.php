<?php

namespace api\modules\telegram\commands;

use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use Longman\TelegramBot\Commands\UserCommand;

class StartCommand extends UserCommand
{

    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $text = TelegramMessage::find()->where(['callback_data' => $this->usage])->one();
        $button = TelegramMessageButton::find()->where()->all();
        return $this->replyToChat("Привет! Я бот!");
    }
}

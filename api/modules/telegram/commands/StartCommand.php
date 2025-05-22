<?php

namespace api\modules\telegram\commands;

use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use common\models\TelegramMessageImage;
use Longman\TelegramBot\Commands\UserCommand;

class StartCommand extends UserCommand
{

    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute(): \Longman\TelegramBot\Entities\ServerResponse
    {
        $text = TelegramMessage::find()->where(['command_id' => $this->usage])->one();
        $image = TelegramMessageImage::find()->where(['telegram_message_id' => $text->id])->all();
        return $this->replyToChat($text->text, $image->image);
    }
}

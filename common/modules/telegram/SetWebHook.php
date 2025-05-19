<?php

namespace common\modules\telegram;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Module;

class SetWebHook extends Module
{
    public string $path = '/admin/telegram/web-hook';

    public function init()
    {
        try {
            // Create Telegram API object
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);

            $hook_url = Yii::$app->request->hostInfo . $this->path;
                $result = $telegram->setWebhook($hook_url);
            if ($result->isOk()) {
                echo $result->getDescription();
            }
        } catch (TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }
}

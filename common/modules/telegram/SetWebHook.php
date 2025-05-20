<?php

namespace common\modules\telegram;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Module;

class SetWebHook extends Module
{

    /**
     * {@inheritdoc}
     */
    public string $path = '/admin/telegram/web-hook';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\telegram\controllers';


    public function init()
    {
        parent::init();
        try {
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);

            $hook_url = Yii::$app->request->hostInfo . $this->path;
                $result = $telegram->setWebhook($hook_url);
            if ($result->isOk()) {
                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result->getDescription(), true));
            }
        } catch (TelegramException $e) {

        }
    }
}

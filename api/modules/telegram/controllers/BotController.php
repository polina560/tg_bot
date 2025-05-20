<?php

namespace api\modules\telegram\controllers;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Controller;

class BotController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public string $path = '/api/telegram/web-hook';

    public function actionSet()
    {
        file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r(Yii::$app->environment->WEB_HOOK_URL, true));

        try {
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);

            $hook_url = Yii::$app->environment->WEB_HOOK_URL;
            $result = $telegram->setWebhook($hook_url);

            if ($result->isOk()) {
                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result->getDescription(), true));
            }
        } catch (TelegramException $e) {
            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($e->getMessage(), true));

        }
    }

    /**
     * {@inheritdoc}
     */
    public function actionUnset()
    {
        try {
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);

            $result = $telegram->deleteWebhook();
            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($result->getDescription(), true));


        } catch (TelegramException $e) {

            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($e->getMessage(), true));
        }
    }
}

<?php

namespace api\modules\telegram\controllers;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
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
        file_put_contents(
            Yii::getAlias('@htdocs/uploads') . '/message.txt',
            print_r(Yii::$app->environment->WEB_HOOK_URL, true)
        );

        try {
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);

            $hook_url = Yii::$app->environment->WEB_HOOK_URL;
            $result = $telegram->setWebhook($hook_url);

            if ($result->isOk()) {
                $commands = [
                    ['command' => 'start', 'description' => 'Запуск бота'],
                    ['command' => 'info', 'description' => 'Информация о боте'],
                    ['command' => 'share', 'description' => 'Поелиться'],
                ];

                $commandResult = Request::setMyCommands([
                    'commands' => json_encode($commands),
                ]);

                file_put_contents(
                    Yii::getAlias('@htdocs/uploads/telegram_log.txt'),
                    "Webhook set: " . print_r($result->getRawData(), true) . "\n" .
                    "Commands set: " . print_r($commandResult->getRawData(), true),
                    FILE_APPEND
                );
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
            file_put_contents(
                Yii::getAlias('@htdocs/uploads') . '/message.txt',
                print_r($result->getDescription(), true)
            );
        } catch (TelegramException $e) {
            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($e->getMessage(), true));
        }
    }
}

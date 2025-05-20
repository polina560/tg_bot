<?php

namespace api\modules\telegram\controllers;

use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Controller;
use yii\web\Response;

class WebHookController extends Controller
{
    public $layout = false; // Важно отключить layout

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        try {
            $telegram = new Telegram(
                Yii::$app->environment->BOT_TOKEN,
                Yii::$app->environment->BOT_USERNAME
            );

            // Отключаем все лишнее
            $telegram->useGetUpdatesWithoutDatabase();

            $telegram->addCommandsPaths([
                Yii::getAlias('@api/modules/telegram/commands'),
            ]);

            return $telegram->handle();

        } catch (\Throwable $e) {
            Yii::error($e);
            return 'error';
        }
    }

}

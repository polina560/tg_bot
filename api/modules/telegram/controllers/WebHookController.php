<?php

namespace api\modules\telegram\controllers;

use common\models\DialogState;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Controller;
use yii\web\Response;

class WebHookController extends Controller
{
    public $layout = false;

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

            $telegram->addCommandsPaths([
                Yii::getAlias('@api/modules/telegram/commands'),
            ]);

            $telegram->useGetUpdatesWithoutDatabase();

            return $telegram->handle();
        } catch (\Throwable $e) {
            Yii::error($e);
            return 'error';
        }
    }



}

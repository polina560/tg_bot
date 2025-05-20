<?php

namespace common\modules\telegram\controllers;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Yii;
use yii\base\Action;

class WebHookController extends Action
{
    public function actionIndex()
    {
//        $data = file_get_contents('php://input');
        file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r('controller', true));

        try {
            $telegram = new Telegram(Yii::$app->environment->BOT_TOKEN, Yii::$app->environment->BOT_USERNAME);
            $data = $telegram->handleGetUpdates();


            if (!empty($data['message']['text'])) {
                $chat_id = $data['message']['from']['id'];
                $user_name = $data['message']['from']['username'];
                $first_name = $data['message']['from']['first_name'];
                $last_name = $data['message']['from']['last_name'];
                $text = trim($data['message']['text']);
                $text_array = explode(" ", $text);

                if ($text == '/help') {
                    $text_return = "Привет, $first_name $last_name, вот команды, что я понимаю:
/help - список команд
/about - о нас
";
                    $this->message_to_telegram(\Yii::$app->environment->BOT_TOKEN, $chat_id, $text_return);
                } elseif ($text == '/about') {
                    $text_return = \Yii::$app->environment->BOT_USERNAME . ":
Я пример самого простого бота для телеграм, написанного на простом PHP.
Мой код можно скачивать, дополнять, исправлять. Код доступен в этой статье:
https://www.novelsite.ru/kak-sozdat-prostogo-bota-dlya-telegram-na-php.html
";
                    $this->message_to_telegram(\Yii::$app->environment->BOT_TOKEN, $chat_id, $text_return);
                }
            }
        } catch (TelegramException $e) {
        }
    }

    public function message_to_telegram($bot_token, $chat_id, $text, $reply_markup = '')
    {
        $ch = curl_init();
        $ch_post = [
            CURLOPT_URL => 'https://api.telegram.org/bot' . $bot_token . '/sendMessage',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $text,
                'reply_markup' => $reply_markup,
            ]
        ];

        curl_setopt_array($ch, $ch_post);
        curl_exec($ch);
    }
}

<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use Codeception\Exception\ModuleException;
use common\components\exceptions\ModelSaveException;
use common\models\DialogState;
use common\models\TelegramMessage;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Yii;

class CallbackqueryCommand extends SystemCommand
{

    protected $name = 'callbackquery';
    protected $description = 'Handle the callback query';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $callback_query = $this->getCallbackQuery();
        $callback_data = $callback_query->getData();
        $user_id = $callback_query->getFrom()->getId();
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();

        switch ($callback_data) {
            case 'get-money':
                return $this->handleAnswerGetMoney($chat_id, $user_id);
            case 'is-member':
                return $this->handleActionIsMember($chat_id, $user_id);
            case 'play_1':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 1);
            case 'play_2':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 2);
            case 'play_3':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 3);
            case 'play_4':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 4);
            case 'play_5':
                return $this->handleActionPlay($chat_id, $user_id, $message_id, 5);
            default:
                return Request::answerCallbackQuery([
                    'callback_query_id' => $callback_query->getId(),
                    'text' => 'Неизвестная команда',
                    'show_alert' => false,
                ]);
        }
    }

    protected function handleAnswerGetMoney($chat_id, $user_id): ServerResponse
    {
        file_put_contents(
            Yii::getAlias('@htdocs/uploads') . '/message.txt',
            print_r('GetMoney', true)
        );
        try {
            // 1. Получаем данные сообщения
            $text = TelegramMessage::find()
                ->where(['key' => 'play'])
                ->andWhere(['serial_number' => 1])
                ->one();

            if (!$text) {
                throw new \Exception('Сообщение не найдено');
            }

            $media_group = TelegramBot::imageToArray($text);
            $mediaResponse = Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group
            ]);

            if (!$mediaResponse->isOk()) {
                file_put_contents(Yii::getAlias('@htdocs/uploads') . '/message.txt', print_r($media_group, true));
                throw new \Exception('Ошибка отправки медиа: ' . $mediaResponse->getDescription());
            }


//             3. Создаем и отправляем сообщение с кнопками
            $inline_keyboard = new InlineKeyboard([
                ['text' => '1', 'callback_data' => 'play_1'],
                ['text' => '2', 'callback_data' => 'play_2'],
                ['text' => '3', 'callback_data' => 'play_3'],
                ['text' => '4', 'callback_data' => 'play_4'],
                ['text' => '5', 'callback_data' => 'play_5'],
            ]);

            $messageResponse = Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Выберите любой вариант',
                'reply_markup' => $inline_keyboard
            ]);

            $dialog = DialogState::find()->where(['user_id' => $user_id])->one();
            if (!empty($dialog)) {
                $dialog->delete();
            }
            $dialog = new DialogState();
            $dialog->user_id = $user_id;
            $dialog->chat_id = $chat_id;
            $dialog->last_msg_time = time();
            $dialog->last_msg_id = 1;

            if (!$dialog->save()) {
                file_put_contents(
                    Yii::getAlias('@htdocs/uploads') . '/message.txt',
                    print_r(new ModelSaveException($dialog), true)
                );
                new ModelSaveException($dialog);
            }

            // 4. Отвечаем на callback запрос
            Request::answerCallbackQuery([
                'text' => 'Монеты будут зачислены скоро!',
                'show_alert' => true,
            ]);

            return $messageResponse;
        } catch (\Exception $e) {
            error_log('Error in handleAnswerGetMoney: ' . $e->getMessage());
            file_put_contents(Yii::getAlias('@htdocs/uploads') . '/error.txt', print_r($e->getMessage(), true));

            // Обязательно отвечаем на callback даже при ошибке
            Request::answerCallbackQuery([
                'text' => 'Произошла ошибка: ' . $e->getMessage(),
                'show_alert' => true,
            ]);

            return Request::sendMessage([
                'chat_id' => $chat_id . $e->getMessage(),
                'text' => 'Произошла ошибка, попробуйте позже'
            ]);
        }
    }

    protected function handleActionIsMember($chat_id, $user_id): ServerResponse
    {
        $member = Request::getChatMember(['chat_id' => $chat_id, 'user_id' => $user_id])->toJson();
        $member = json_decode($member, true);
        $status = $member['result']['status'];
        $member_statuses = ['creator', 'administrator', 'member'];

        if (!in_array($status, $member_statuses)) {
            $text = TelegramMessage::find()->where(['key' => 'notMember'])->one();
            if ($media_group = TelegramBot::imageToArray($text)) {
                $result = Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $text->text,
                    'reply_markup' => new InlineKeyboard([
                        ['text' => 'Подписался', 'callback_data' => 'is-member']
                    ])
                ]);

                return $result;
            }
        }

        return $this->handleAnswerGetMoney($chat_id, $user_id);
    }

    protected function handleActionPlay($chat_id, $user_id, $message_id, int $number): ServerResponse
    {
        //TODO: проверка на конец опроса
        $dialog = DialogState::find()->where(['user_id' => $user_id])->one();
        $dialog->last_msg_id += 0;
        if ($dialog->last_msg_id >= 5) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'конец'
            ]);
        }
        switch ($number) {
            case 1:
                //TODO: запомнить выбор
            case 2:
                //TODO: запомнить выбор
            case 3:

                //TODO: запомнить выбор
            case 4:

                //TODO: запомнить выбор
            case 5:

                //TODO: запомнить выбор
            default:
                break;
        }
        $dialog->last_msg_id += 1;
        $dialog->last_msg_time = time();
        if (!$dialog->save()) {
            throw new ModelSaveException($dialog);
        }

        Request::deleteMessage([
           'chat_id' => $chat_id,
           'message_id' => $message_id
        ]);

        $text = TelegramMessage::find()->where(['key' => 'play'])->andWhere(['serial_number' => $dialog->last_msg_id]
        )->one();
        if ($text) {
            if ($media_group = TelegramBot::imageToArray($text)) {
                $mediaResponse = Request::sendMediaGroup([
                    'chat_id' => $chat_id,
                    'media' => $media_group
                ]);
            } else {
                $mediaResponse = Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $text->text
                ]);
            }
        }

        $inline_keyboard = new InlineKeyboard([
            ['text' => '1', 'callback_data' => 'play_1'],
            ['text' => '2', 'callback_data' => 'play_2'],
            ['text' => '3', 'callback_data' => 'play_3'],
            ['text' => '4', 'callback_data' => 'play_4'],
            ['text' => '5', 'callback_data' => 'play_5'],
        ]);

        $messageResponse = Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите любой вариант' . '(' . $number . ')',
            'reply_markup' => $inline_keyboard
        ]);

        // 4. Отвечаем на callback запрос
        Request::answerCallbackQuery([
            'text' => 'Ответ принят',
            'show_alert' => true,
        ]);

        return $messageResponse;
    }
}

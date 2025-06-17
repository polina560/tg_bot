<?php

namespace api\modules\telegram\commands;

use api\modules\telegram\TelegramBot;
use Codeception\Exception\ModuleException;
use common\components\exceptions\ModelSaveException;
use common\models\DialogState;
use common\models\TelegramButton;
use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Yii;
use yii\db\Expression;

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
        try {
            $text = TelegramMessage::find()
                ->where(['key' => 'play'])
                ->andWhere(['serial_number' => 1])
                ->one();
            if (!$text) {
                throw new \Exception('Сообщение не найдено');
            }

            $messageResponse = $this->sendTestMessage($text, $chat_id);

            $dialog = DialogState::find()->where(['user_id' => $user_id])->one();
            if (!empty($dialog)) {
                $dialog->delete();
            }
            $dialog = new DialogState();
            $dialog->user_id = $user_id;
            $dialog->chat_id = $chat_id;
            $dialog->last_msg_time = time();
            $dialog->last_msg_id = 1;
            $dialog->remainder = 5000; //TODO: занести значения в БД

            if (!$dialog->save()) {
                file_put_contents(
                    Yii::getAlias('@htdocs/uploads') . '/message.txt',
                    print_r(new ModelSaveException($dialog), true)
                );
                new ModelSaveException($dialog);
            }

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
        $member = Request::getChatMember(['chat_id' => Yii::$app->environment->CHAT_ID, 'user_id' => $user_id])->toJson();
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
        //проверка на конец теста
        $dialog = DialogState::find()->where(['user_id' => $user_id])->one();

        //обработка нажатия на кнопку
        $last_text = TelegramMessage::find()->where(['key' => 'play'])->andWhere(
            ['serial_number' => $dialog->last_msg_id]
        )->one();
        $button = TelegramMessageButton::find()->where(['telegram_message_id' => $last_text->id])->andWhere(
            ['serial_number' => $number]
        )->one();
        if (!$button) {
            throw new \Exception('Сообщение не найдено');
        }

        $price = $button->value;
        if ($price <= $dialog->remainder) {
            $remainder = $dialog->remainder - $price;
            switch ($number) {
                case 1:
                    $dialog->ans_1 += 1;
                    break;
                case 2:
                    $dialog->ans_2 += 1;
                    break;
                case 3:
                    $dialog->ans_3 += 1;
                    break;
                case 4:
                    $dialog->ans_4 += 1;
                    break;
                case 5:
                    $dialog->ans_5 += 1;
                    break;
                default:
                    break;
            }
            $dialog->remainder = $remainder;
            $dialog->last_msg_id += 1;
            $dialog->last_msg_time = time();
            if (!$dialog->save()) {
                throw new ModelSaveException($dialog);
            }
        } else {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'нет монет'
            ]);
        }


        Request::deleteMessage([
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]);


        if ($dialog->last_msg_id > 5) {
            return $this->sendEndTestMessage($chat_id, $dialog);
        }

        //отправка нового сообщения
        $text = TelegramMessage::find()->where(['key' => 'play'])->andWhere(['serial_number' => $dialog->last_msg_id]
        )->one();
        if (!$text) {
            throw new \Exception('Сообщение не найдено');
        }

        return $this->sendTestMessage($text, $chat_id, $price, $remainder);
    }

    protected function sendTestMessage($text, $chat_id, $price = 0, $remainder = 0)
    {
        if ($media_group = TelegramBot::imageToArray($text, $price, $remainder)) {
            Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group
            ]);
        } else {
            Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => sprintf($text->text, $price, $remainder)
            ]);
        }

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Выберите любой вариант',
            'reply_markup' => self::keyboard()
        ]);
    }

    protected function sendEndTestMessage($chat_id, DialogState $dialog)
    {
        $array = array($dialog->ans_1, $dialog->ans_2, $dialog->ans_3, $dialog->ans_4, $dialog->ans_5);
        $max = max($array);
        $indexes = array_keys($array, $max);
        foreach ($indexes as $index)
            $serial_numbers[] = ++$index;


        $text = TelegramMessage::find()->where(['key' => '/res'])->andWhere(['IN', 'serial_number', $serial_numbers])->orderBy(
            new Expression('rand()')
        )
            ->limit(1)->one();

        $keyboard = array(
            "resize_keyboard" => true,
            "inline_keyboard" => array(
                array(
                    array(
                        'text' => 'Поделиться',
                        'switch_inline_query' => 'telegram bot',
                    ),
                ),
                array(
                    array(
                        'text' => 'Получить монеты!',
                        'callback_data' => 'get-money'
                    ),
                ),
            ),
        );

        if ($media_group = TelegramBot::imageToArray($text)) {
            Request::sendMediaGroup([
                'chat_id' => $chat_id,
                'media' => $media_group
            ]);
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Поделиться',
                'reply_markup' => $keyboard
            ]);
        } else {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $text->text,
                'reply_markup' => $keyboard
            ]);
        }

    }

    static function keyboard()
    {
        return new InlineKeyboard([
            ['text' => '1', 'callback_data' => 'play_1'],
            ['text' => '2', 'callback_data' => 'play_2'],
            ['text' => '3', 'callback_data' => 'play_3'],
            ['text' => '4', 'callback_data' => 'play_4'],
            ['text' => '5', 'callback_data' => 'play_5'],
        ]);
    }
}

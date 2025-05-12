<?php

namespace common\modules\mail\components;

use common\components\{UserView};
use common\enums\AppType;
use common\models\Setting;
use common\modules\mail\{enums\LogStatus, models\MailingLog, models\Template};
use common\modules\user\models\User;
use Pug\Yii\ViewRenderer;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\symfonymailer\Mailer as YiiMailer;

/**
 * Расширенный компонент yii\symfonyMailer\Mailer
 *
 * При выполнении compose письма он оборачивает все данные передаваемые в шаблоны в массив $data.
 * Добавляет в него переменные `domain` и `username`, если их не задали явно
 *
 * Для отправки письма вызвать:
 *
 * ```php
 * Yii::$app->mailer->compose('email-confirm', [], $emailValue)->sendAsync();
 * ```
 */
class Mailer extends YiiMailer
{
    /**
     * Задержка в секундах между отправками
     */
    public int $delay = 2;

    public array $from = [];

    /**
     * Если true, то отправка письма будет добавлена в очередь
     */
    public bool $pushToQueue = false;

    public $messageClass = Message::class;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->setTransport([
            'scheme' => 'smtp',
            'host' => $host = Yii::$app->environment->EMAIL_HOST ?: Setting::getParameterValue('email_server'),
            'port' => $port = (int)(Yii::$app->environment->EMAIL_PORT ?: Setting::getParameterValue('email_port')),
            'username' => Yii::$app->environment->EMAIL_USERNAME ?: Setting::getParameterValue('email_username'),
            'password' => Yii::$app->environment->EMAIL_PASSWORD ?: Setting::getParameterValue('email_password'),
            'options' => $port === 25
                ? ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]
                : ['ssl' => true]
        ]);

        // Проверка на использование файлового транспорта писем
        $this->useFileTransport = YII_ENV_TEST || empty($host) || $host === 'smtp.test.ru';

        // установка адреса отправителя
        $nameFrom = Yii::$app->environment->EMAIL_NAME_FROM ?: Setting::getParameterValue('email_name_from');
        $from = Yii::$app->environment->EMAIL_FROM ?: Setting::getParameterValue('email_from');
        $this->from = !empty($nameFrom) ? [$from => $nameFrom] : [$from];

        // установка пути к шаблонам
        $this->setViewPath('@common/modules/mail/templates');
        $this->setView(['class' => UserView::class, 'renderers' => ['pug' => ViewRenderer::class]]);
    }

    /**
     * {@inheritdoc}
     */
    public function compose($view = null, array $params = [], array|string $to = null, User $user = null): Message
    {
        $template = null;
        if (is_string($view) && in_array($view, Template::findAll(), true)) {
            $template = $view;
            $view = [
                'html' => "$view-html.php",
                'text' => "$view-text.php"
            ];
        }
        if (!isset($params['domain'])) {
            $params['domain'] = Yii::$app->request->hostInfo;
        }
        if (!$user) {
            $user = User::findIdentityByEmail($to);
        }
        if (is_string($to) && !isset($params['username']) && $user) {
            $params['username'] = $user->userExt->first_name . ' ' . $user->userExt->last_name;
            if ($params['username'] === ' ') {
                $params['username'] = $user->username;
            }
        }
        /** @var Message $message */
        $message = parent::compose($view, ['data' => $params, 'user' => $user]);
        $message->data = $params;
        if ($message->appType === AppType::Undefined) {
            $message->appType = AppType::fromAppId(Yii::$app->id);
        }
        $message->template = $template;
        $message->setFrom($this->from);
        if ($to) {
            $message->setTo($to);
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     * @param Message $message
     *
     * @throws InvalidConfigException
     */
    public function afterSend($message, $isSuccessful): void
    {
        parent::afterSend($message, $isSuccessful);
        $to = $message->getTo();
        if (is_array($to)) {
            $to = implode(', ', array_keys($to));
        }
        $this->_saveLog([
            'template' => $message->template,
            'mailing_subject' => $message->getSubject(),
            'mail_to' => $to,
            'status' => $isSuccessful ? LogStatus::Success->value : LogStatus::Error->value,
            'app_type' => $message->appType->value
        ], $isSuccessful ? 'Успешно' : 'Ошибка', $message->previousLogId, $message->data);
    }

    /**
     * Сохранение лога отправки
     *
     * @param array       $log           Данные лога
     * @param string|null $description   Описание лога
     * @param int|null    $previousLogId ID предыдущего лога
     * @param array       $data          Данные переданные в шаблон
     *
     * @throws InvalidConfigException
     */
    private function _saveLog(array $log, string $description = null, int $previousLogId = null, array $data = []): void
    {
        $model = new MailingLog();
        $model->load($log, '');
        $model->date = time();
        $model->data = Json::encode($data);
        if ($user = User::findIdentityByEmail($model->mail_to)) {
            $model->user_id = $user->id; // Добавляем ссылку на пользователя, чтобы было проще переходить к нему
        }
        if ($description) {
            $model->description = mb_strimwidth($description, 0, 200, '...');
        }
        // Обновление статуса предыдущего лога
        if ($previousLogId) {
            /** @var MailingLog $old_model */
            $old_model = MailingLog::find()->where(['id' => $previousLogId])->one();
            $old_model->status = LogStatus::Repeated->value;
            if ($old_model->save()) {
                $model->mailing_log_id = $previousLogId;
            }
        }
        if ($model->save()) {
            return;
        }
        Yii::error(
            <<<HEREDOC
saveLog fail,
mailing_subject=$model->mailing_subject,
mail_to=$model->mail_to,
status=$model->status,
app_type=$model->app_type
HEREDOC
            ,
            __METHOD__
        );
    }
}

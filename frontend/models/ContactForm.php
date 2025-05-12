<?php

namespace frontend\models;

use common\models\AppModel;
use common\widgets\reCaptcha\ReCaptchaValidator3;
use Yii;

/**
 * ContactForm is the model behind the contact form.
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ContactForm extends AppModel
{
    /**
     * Имя
     */
    public ?string $name = null;

    /**
     * Email адрес
     */
    public ?string $email = null;

    /**
     * Тема
     */
    public ?string $subject = null;

    /**
     * Текст
     */
    public ?string $body = null;

    /**
     * Код подтверждения captcha
     */
    public ?string $reCaptcha = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email']
        ];
        if (!YII_ENV_TEST && !empty(Yii::$app->reCaptcha->secretV3)) {
            $rules[] = ['reCaptcha', 'required'];
            $rules[] = ['reCaptcha', ReCaptchaValidator3::class, 'action' => false];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'email' => Yii::t('app', 'Email'),
            'subject' => Yii::t('app', 'Subject'),
            'body' => Yii::t('app', 'Body')
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     *
     * @return bool whether the email was sent
     */
    public function sendEmail(string $email): bool
    {
        return Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setReplyTo([$this->email => $this->name])
            ->setSubject($this->subject)
            ->setTextBody($this->body)
            ->send();
    }
}

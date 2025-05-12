<?php

namespace frontend\models;

use common\models\AppModel;
use common\modules\user\{enums\Status, models\Email, models\User};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;

/**
 * Class ResendVerificationEmailForm
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ResendVerificationEmailForm extends AppModel
{
    /**
     * Email адрес
     */
    public ?string $email = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email',
                'exist',
                'targetClass' => Email::class,
                'targetAttribute' => 'value',
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('app', 'Email'),
        ];
    }

    /**
     * Sends confirmation email to user
     *
     * @return bool whether the email was sent
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function sendEmail(): bool
    {
        /** @var User $user */
        $user = User::find()
            ->joinWith('email')
            ->where(['status' => Status::New->value, 'value' => $this->email])
            ->one();

        if (!isset($user)) {
            $this->addError('email', 'Пользователь не найден');
            return false;
        }
        $user->email->sendVerificationEmail(false);
        return true;
    }
}

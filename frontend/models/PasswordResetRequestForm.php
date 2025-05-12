<?php

namespace frontend\models;

use common\enums\AppType;
use common\models\AppModel;
use common\modules\mail\models\Mailing;
use common\modules\user\{enums\PasswordRestoreType, enums\Status, models\Email, models\User, Module};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\NotFoundHttpException;

/**
 * Password reset request form
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class PasswordResetRequestForm extends AppModel
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
            'email' => Yii::t('app', 'Email')
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was sent
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function sendEmail(): bool
    {
        /** @var User $user */
        $user = User::find()
            ->joinWith('email')
            ->where(['status' => Status::Active->value, 'value' => $this->email])
            ->one();

        if (!isset($user)) {
            $this->addError('email', 'Пользователь не найден');
            return false;
        }

        return $user->resetPassword();
    }
}

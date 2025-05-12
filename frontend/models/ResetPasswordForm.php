<?php

namespace frontend\models;

use common\models\AppModel;
use common\modules\user\models\User;
use Yii;
use yii\base\{Exception, InvalidArgumentException};
use yii\web\HttpException;

/**
 * Password reset form
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ResetPasswordForm extends AppModel
{
    /**
     * Пароль
     */
    public ?string $password = null;

    /**
     * Объект найденного пользователя
     */
    private ?User $_user;

    /**
     * Creates a form model given a token.
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
     *
     * @throws HttpException
     */
    public function __construct(string $token, array $config = [])
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        $this->_user = User::findIdentityByPasswordResetToken($token);
        if (!isset($this->_user)) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'password' => Yii::t('app', 'Password'),
        ];
    }

    /**
     * Resets password.
     *
     * @return bool if the password was reset.
     *
     * @throws Exception
     */
    public function resetPassword(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}

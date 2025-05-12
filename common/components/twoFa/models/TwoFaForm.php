<?php

namespace common\components\twoFa\models;

use common\components\twoFa\behaviors\TwoFaBehavior;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Yii;
use yii\base\Model;
use yii\web\IdentityInterface;

/**
 * Enable Two-Factor Authentication form
 */
class TwoFaForm extends Model
{
    /**
     * @var string Scenario defaults to "default". Otherwise, override the constructor or init.
     * @see https://github.com/yiisoft/yii2/issues/12707
     */
    const SCENARIO_ACTIVATE = self::SCENARIO_DEFAULT; // Сценарий для активации 2FA
    const SCENARIO_LOGIN = 'login'; // Сценарий для входа

    /** The generated secret */
    public string $secret; // Секретный ключ для 2FA

    /** The code entered by the user */
    public string $code = ''; // Код, введенный пользователем

    /** Keeps the user logged in. */
    public bool $rememberMe = true; // Флаг, указывающий, нужно ли запоминать пользователя

    /** Time window in which the key is valid. Leave this null to use the default component setting. */
    public ?int $window = null; // Временное окно для проверки кода

    private IdentityInterface|TwoFaBehavior $_user; // Пользователь, связанный с формой

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['secret', 'code'], 'required'],
            ['code', 'filter', 'filter' => 'trim'],
            ['code', 'string', 'min' => 6],
            ['code', 'validateCode'],
            ['rememberMe', 'required', 'on' => self::SCENARIO_LOGIN],
            ['rememberMe', 'boolean', 'on' => self::SCENARIO_LOGIN],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return ['code' => Yii::t('app', 'Code')];
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function validateCode($attribute, $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser(); // Получение пользователя
            if (!$user->validateTwoFaCode($this->code, $this->secret, $this->window)) {
                $this->addError($attribute, 'Incorrect code.'); // Добавление ошибки, если код некорректен
            }
        }
    }

    public function getUser(): IdentityInterface|TwoFaBehavior
    {
        return $this->_user; // Возвращает текущего пользователя
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function setUser(IdentityInterface|TwoFaBehavior $user): void
    {
        $this->_user = $user; // Устанавливает текущего пользователя
        $this->secret = $user->hasTwoFaEnabled() ? $user->getTwoFaSecret() : $user->generateTwoFaSecret(); // Устанавливает секретный ключ
    }

    /**
     * Logs in a user using the provided code.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0); // Вход пользователя с учетом флага rememberMe
        }

        return false;
    }

    /**
     * Enables Two Factor Authentication for a user.
     *
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $user->enableTwoFa($this->secret); // Включение 2FA для пользователя

            return !$user->hasErrors(); // Возвращает true, если нет ошибок у пользователя
        }

        return false;
    }
}

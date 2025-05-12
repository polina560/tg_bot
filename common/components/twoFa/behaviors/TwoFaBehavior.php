<?php

namespace common\components\twoFa\behaviors;

use common\components\twoFa\TwoFa;
use PragmaRX\Google2FA\Exceptions\{IncompatibleWithGoogleAuthenticatorException,
    InvalidCharactersException,
    SecretKeyTooShortException};
use Yii;
use yii\base\{Behavior, InvalidConfigException};
use yii\db\BaseActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property IdentityInterface $owner
 * @property-read null|string  $twoFaSecret
 */
class TwoFaBehavior extends Behavior
{
    /**
     * The attribute that will receive secret value
     */
    public string $secretAttribute = 'totp_secret';

    /**
     * The Yii2 component name, as defined in config
     */
    public string $twoFaComponent = 'twoFa';

    /**
     * Флаг, указывающий, включена ли 2FA
     */
    public bool $twoFaEnabled;

    /**
     * Экземпляр класса TwoFa
     */
    private TwoFa $twoFa;

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_FIND => 'initAttributes',
        ];
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->twoFa = Yii::$app->get($this->twoFaComponent);
    }

    /**
     * Fill the twoFaEnabled variable.
     * Do this afterFind, so that we can set a new secret runtime without marking 2Fa enabled.
     */
    public function initAttributes(): void
    {
        if (!isset($this->twoFaEnabled)) {
            $this->twoFaEnabled = $this->getTwoFaSecret() !== null;
        }
    }

    /**
     * Получение секретного ключа из атрибута модели
     */
    public function getTwoFaSecret(): ?string
    {
        return $this->owner->{$this->secretAttribute};
    }

    /**
     * Возвращает статус включенности 2FA
     */
    public function hasTwoFaEnabled(): bool
    {
        return $this->twoFaEnabled;
    }

    /**
     * Генерация нового секретного ключа
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function generateTwoFaSecret(): string
    {
        return $this->twoFa->generateSecret();
    }

    /**
     * Включение 2FA с заданным или новым секретом
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function enableTwoFa(string $secret = null)
    {
        return $this->owner->updateAttributes([$this->secretAttribute => $secret ?? $this->generateTwoFaSecret()]);
    }

    /**
     * Отключение 2FA, удаляя секретный ключ
     */
    public function disableTwoFa()
    {
        return $this->owner->updateAttributes([$this->secretAttribute => null]);
    }

    /**
     * Проверка введенного кода на валидность
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function validateTwoFaCode(string $code, ?string $secret = null, ?int $window = null): bool
    {
        return $this->twoFa->checkCode($secret ?? $this->getTwoFaSecret(), $code, $window);
    }

    /**
     * Получение текущего кода 2FA
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function getCurrentTwoFaCode(string $secret = null): string
    {
        return $this->twoFa->getCurrentCode($secret ?? $this->getTwoFaSecret());
    }
}

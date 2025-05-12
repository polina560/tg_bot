<?php

namespace common\components\twoFa;

use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;

/**
 *
 * @property-read IdentityInterface|null $identityFromLoginVerificationSession
 */
class User extends \yii\web\User
{
    public string $loginVerificationSessionKey = 'loginVerification'; // Ключ сессии для хранения данных проверки входа

    /**
     * {@inheritdoc}
     * @param IdentityInterface $identity the user identity information
     * @param bool $cookieBased whether the login is cookie-based
     * @param int $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        $this->destroyLoginVerificationSession(); // Удаление сессии проверки входа после успешного логина

        parent::afterLogin($identity, $cookieBased, $duration); // Вызов родительского метода
    }

    protected function hasValidLoginVerificationSession(): bool
    {
        $data = Yii::$app->session->get($this->loginVerificationSessionKey); // Получение данных из сессии
        if ($data === null) {
            return false; // Если данные отсутствуют, возвращаем false
        }
        if (is_array($data) && count($data) == 3) {
            // Проверяем, что данные являются массивом нужной длины
            if (time() < $data['exp']) {
                // Проверяем, не истек ли срок действия сессии
                return true;
            }
        }
        $this->destroyLoginVerificationSession(); // Удаляем недействительную сессию

        return false;
    }

    /**
     * This method attempts to authenticate a user using the information in the login verification session.
     *
     * @return IdentityInterface|null Returns an 'identity' if valid, otherwise null.
     */
    public function getIdentityFromLoginVerificationSession(): ?IdentityInterface
    {
        if ($this->hasValidLoginVerificationSession()) { // Проверяем, есть ли действительная сессия проверки входа
            $data = Yii::$app->session->get($this->loginVerificationSessionKey); // Получаем данные из сессии
            /* @var $class IdentityInterface|string */
            $class = $this->identityClass; // Получаем класс идентичности пользователя
            $identity = $class::findIdentity($data['id']); // Ищем идентичность по ID из сессии
            if ($identity !== null) {
                if (!$identity instanceof IdentityInterface) { // Проверяем, что идентичность соответствует интерфейсу
                    throw new InvalidValueException("$class::findIdentity() must return an object implementing IdentityInterface.");
                }
                if ($data['returnUrl']) {  // Если есть URL для перенаправления, устанавливаем его
                    $this->setReturnUrl($data['returnUrl']);
                }

                return $identity;
            }
        }
        $this->destroyLoginVerificationSession(); // Удаляем недействительную сессию

        return null;
    }

    /**
     * @param IdentityInterface $identity
     * @param string|null $returnUrl The Url the user should be redirected to after a valid login verification attempt
     * @param int|null $expirationTime The verification ID is valid till this Unix timestamp. Defaults to 5 minutes in the future
     */
    public function createLoginVerificationSession(IdentityInterface $identity, ?string $returnUrl = null, ?int $expirationTime = null): void
    {
        if ($expirationTime === null) {
            $expirationTime = time() + (5 * 60);
        }

        Yii::$app->session->set($this->loginVerificationSessionKey, [
            'id' => $identity->getId(),
            'exp' => $expirationTime,
            'returnUrl' => $this->getReturnUrl($returnUrl),
        ]);
    }

    public function destroyLoginVerificationSession(): void
    {
        Yii::$app->session->remove($this->loginVerificationSessionKey); // Удаляем сессию проверки входа
    }
}

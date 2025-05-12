<?php

namespace common\components\twoFa;

use BaconQrCode\Renderer\{Image\ImagickImageBackEnd, ImageRenderer, RendererStyle\RendererStyle};
use BaconQrCode\Writer;
use PragmaRX\Google2FA\{Exceptions\IncompatibleWithGoogleAuthenticatorException,
    Exceptions\InvalidCharactersException,
    Exceptions\SecretKeyTooShortException,
    Google2FA};
use yii\base\Component;

class TwoFa extends Component
{
    private Google2FA $g; // Экземпляр Google2FA

    public int $secretLength = 16; // Длина генерируемого секрета

    public int $window = 4;  // Временное окно для проверки кода

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->g = new Google2FA(); // Инициализация экземпляра Google2FA
    }

    /**
     * Генерация секретного ключа.
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function generateSecret(): string
    {
        return $this->g->generateSecretKey($this->secretLength); // Генерация секретного ключа заданной длины
    }

    /**
     * Проверка кода на соответствие секрету.
     *
     * @param string   $secret Секретный ключ
     * @param string   $code   Код, введенный пользователем
     * @param int|null $window Временное окно для проверки кода
     *
     * @return bool Успешность проверки
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function checkCode(string $secret, string $code, int $window = null): bool
    {
        $window = $window === null ? $this->window : $window; // Использование заданного или стандартного окна

        return $this->g->verifyKey($secret, $code, $window);  // Проверка кода на соответствие секрету
    }

    /**
     * Получение текущего кода на основе секрета.
     *
     * @param string $secret Секретный ключ
     *
     * @return string Текущий OTP код
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function getCurrentCode(string $secret): string
    {
        return $this->g->getCurrentOtp($secret); // Получение текущего одноразового пароля (OTP)
    }

    /**
     * Генерация QR-кода для сканирования.
     *
     * @param string $issuer      Название приложения или сервиса
     * @param string $accountName Имя учетной записи пользователя
     * @param string $secret      Секретный ключ
     * @param int    $size        Размер QR-кода
     *
     * @return string QR-код в формате base64
     */
    public function generateQrCodeInline(string $issuer, string $accountName, string $secret, int $size = 200): string
    {
        $url = $this->g->getQRCodeUrl($issuer, $accountName, $secret); // Получение URL для QR-кода
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400), // Стиль рендеринга с размером 400x400
                new ImagickImageBackEnd(), // Использование Imagick для генерации изображения
            ),
        );

        return base64_encode($writer->writeString($url)); // Возвращает QR-код в формате base64
    }
}

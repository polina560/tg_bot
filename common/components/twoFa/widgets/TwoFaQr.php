<?php

namespace common\components\twoFa\widgets;

use common\components\twoFa\TwoFa;
use Yii;
use yii\base\Widget;

class TwoFaQr extends Widget
{
    public string $twoFaComponent = 'twoFa'; // Имя компонента 2FA по умолчанию
    public $secret; // Секретный код для 2FA
    public $accountName;
    public ?string $issuer = null; // Эмитент (может быть null)
    public bool $showSecret = true;  // Показать секретный код или нет
    public int $size = 200; // Размер QR-кода в пикселях

    /** @var \common\components\twoFa\TwoFa */
    private TwoFa $twoFa; // Экземпляр класса TwoFa

    public function init()
    {
        parent::init();
        $this->twoFa = Yii::$app->get($this->twoFaComponent); // Получение компонента 2FA из приложения
        $this->issuer = $this->issuer === null ? Yii::$app->name : $this->issuer; // Установка эмитента
    }

    public function run()
    {
        $twoFaQrCodeUrl = $this->twoFa->generateQrCodeInline( // Генерация URL QR-кода
            $this->issuer,
            $this->accountName,
            $this->secret
        );
        $this->renderWidget($this->secret, $twoFaQrCodeUrl, $this->size); // Отображение виджета
    }

    public function renderWidget(string $secret, string $twoFaQrCodeUrl, int $size): void
    {
        ?>
        <div>
            <!-- Отображение QR-кода -->
            <img src="data:image/png;base64,<?= $twoFaQrCodeUrl ?>" alt="<?= $secret ?>" width="<?= $size ?>" />
        </div>
        <?php
        if ($this->showSecret) { ?> <!-- Условие для отображения секрета -->
            <p><?= Yii::t('app', 'Or you can also enter the secret manually:') ?>
            <pre><?= $secret; ?></pre>
            </p>
            <?php
        }
    }
}

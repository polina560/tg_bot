<?php

use common\components\twoFa\models\TwoFaForm;
use common\components\twoFa\widgets\TwoFaQr;
use common\widgets\AppActiveForm;
use yii\helpers\Html;

/* @var TwoFaForm $model */
?>
<div class="row">
    <div class="col-lg-6">
        <?php
        $form = AppActiveForm::begin(['id' => 'enable-2fa-form']);
        echo Yii::t('app', 'Scan the following QR code using a TOTP compatible app, like Google Authenticator or Authy.');
        ?>
        <br />
        <?php
        // Генерация QR-кода
        echo TwoFaQr::widget([
            'accountName' => $model->getUser()->email,
            'secret' => $model->secret, // Секретный ключ для генерации кодов
            'size' => 300, // Размер QR-кода в пикселях
        ]);
        // Скрытое поле для хранения секретного ключа
        echo $form->field($model, 'secret')->hiddenInput()->label(false);
        ?>
        <br />
        <?= Yii::t('app', 'Enter the generated code to enable two-factor authentication:') ?>
        <?= $form->field($model, 'code') // Поле ввода для кода подтверждения
            ->textInput([
                'autofocus' => true,
                'placeholder' => $model->getAttributeLabel('code'),
                'autocomplete' => 'off', // Отключение автозаполнения браузера
            ])
            ->label(false);
        ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Enable'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php AppActiveForm::end(); ?>
    </div>
</div>

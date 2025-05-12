<?php

use common\components\twoFa\models\TwoFaForm;
use common\widgets\AppActiveForm;
use yii\helpers\Html;

/**
 * @var TwoFaForm $model
 */

$form = AppActiveForm::begin(['id' => 'login-verification-form']); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <h4><?= Yii::t('app', 'Login Verification') ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'code')->label(false)->textInput([ // Поле ввода для кода верификации
                            'autofocus' => true,
                            'class' => 'form-control',
                            'autocomplete' => 'off', // Отключение автозаполнения браузера
                            'placeholder' => Yii::t('app', 'Enter your verification code'),
                        ]) ?>
                    </div>
                    <div class="row">
                        <div class="col-sm-5">
                            <?= Html::a(Yii::t('app', 'Cancel'), ['login'], ['class' => 'btn btn-secondary btn-block']) ?>
                        </div>
                        <div class="col-sm-7 text-end">
                            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-block']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php AppActiveForm::end(); ?>

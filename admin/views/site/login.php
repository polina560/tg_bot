<?php

use common\widgets\AppActiveForm;
use common\widgets\reCaptcha\ReCaptcha3;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

/**
 * @var $this  yii\web\View
 * @var $form  AppActiveForm
 * @var $model admin\models\LoginForm
 */

$this->title = 'Войти';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <h4><?= Html::encode($this->title) ?></h4>
                </div>
                <div class="card-body">
                    <p>Пожалуйста заполните указанные ниже поля:</p>
                    <?php
                    $form = AppActiveForm::begin(['id' => 'login-form']) ?>
                    <div class="form-group mb-3">

                        <?= $form
                            ->field($model, 'username')
                            ->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>

                        <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'password']) ?>

                        <?= $form->field($model, 'rememberMe')->checkbox() ?>

                        <?php
                        if (!YII_ENV_TEST): ?>
                            <?php
                            if (!empty(Yii::$app->reCaptcha->siteKeyV3)): ?>
                                <?= $form->field($model, 'reCaptcha')->label(false)->widget(ReCaptcha3::class) ?>
                            <?php
                            else: ?>
                                <?= $form->field($model, 'verifyCode')->widget(Captcha::class) ?>
                            <?php
                            endif ?>
                        <?php
                        endif ?>
                    </div>
                    <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    <?php
                    AppActiveForm::end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

use common\widgets\AppActiveForm;
use common\widgets\reCaptcha\ReCaptcha3;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $form  \common\widgets\AppActiveForm
 * @var $model \common\modules\user\models\LoginForm
 */

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'Please fill out the following fields to login:') ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = AppActiveForm::begin(['id' => 'login-form']) ?>

            <?= $form->field($model, 'login')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>

            <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'password']) ?>

            <?= $form->field($model, 'rememberMe')->checkbox() ?>

            <?= !YII_ENV_TEST && !empty(Yii::$app->reCaptcha->siteKeyV3)
                ? $form->field($model, 'reCaptcha')->label(false)->widget(ReCaptcha3::class)
                : null ?>

            <div style="color:#999;margin:1em 0">
                <?= Yii::t('app', 'If you forgot your password you can ') ?><?= Html::a(
                    Yii::t('app', 'reset it'),
                    ['/request-password-reset']
                ) ?>.
                <br>
                <?= Yii::t('app', 'Need new verification email? ') ?><?= Html::a(
                    Yii::t('app', 'Resend'),
                    ['/resend-verification-email']
                ) ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton(
                    Yii::t('app', 'Login'),
                    ['class' => 'btn btn-primary', 'name' => 'login-button']
                ) ?>
            </div>

            <?php AppActiveForm::end() ?>
        </div>
    </div>
</div>

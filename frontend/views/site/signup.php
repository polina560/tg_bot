<?php

use common\widgets\AppActiveForm;
use common\widgets\reCaptcha\ReCaptcha3;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $form  \common\widgets\AppActiveForm
 * @var $model \common\modules\user\models\SignupForm
 */

$this->title = Yii::t('app', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'Please fill out the following fields to signup:') ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = AppActiveForm::begin(['id' => 'form-signup']) ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>

            <?= $form->field($model, 'email')->textInput(['autocomplete' => 'email']) ?>

            <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password']) ?>

            <?= $form->field($model, 'rules_accepted')->checkbox() ?>

            <?= !YII_ENV_TEST && !empty(Yii::$app->reCaptcha->siteKeyV3)
                ? $form->field($model, 'reCaptcha')->label(false)->widget(ReCaptcha3::class)
                : null ?>

            <div class="form-group">
                <?= Html::submitButton(
                    Yii::t('app', 'Signup'),
                    ['class' => 'btn btn-primary', 'name' => 'signup-button']
                ) ?>
            </div>

            <?php AppActiveForm::end() ?>
        </div>
    </div>
</div>

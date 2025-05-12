<?php

use common\widgets\AppActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $form  \common\widgets\AppActiveForm
 * @var $model frontend\models\PasswordResetRequestForm
 */

$this->title = Yii::t('app', 'Request password reset');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('app', 'Please fill out your email. A link to reset password will be sent there.') ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = AppActiveForm::begin(['id' => 'request-password-reset-form']) ?>

            <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'autocomplete' => 'email']) ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-primary']) ?>
            </div>

            <?php AppActiveForm::end() ?>
        </div>
    </div>
</div>

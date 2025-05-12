<?php

use common\widgets\AppActiveForm;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $form  \common\widgets\AppActiveForm
 * @var $model frontend\models\ResetPasswordForm
 */

$this->title = Yii::t('app', 'Reset password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please choose your new password:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = AppActiveForm::begin(['id' => 'reset-password-form']) ?>

            <?= $form->field($model, 'password')
                ->passwordInput(['autofocus' => true, 'autocomplete' => 'new-password']) ?>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php AppActiveForm::end() ?>
        </div>
    </div>
</div>

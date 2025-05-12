<?php

use common\widgets\reCaptcha\ReCaptcha3;
use yii\bootstrap5\{ActiveForm, Html};

/**
 * @var $this  yii\web\View
 * @var $form  yii\bootstrap5\ActiveForm
 * @var $model frontend\models\ContactForm
 */

$this->title = Yii::t('app', 'Contact');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Yii::t(
            'app',
            'If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.'
        ) ?>
    </p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'contact-form']) ?>

            <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'email')->textInput(['placeholder' => 'name@example.com']) ?>

            <?= $form->field($model, 'subject') ?>

            <?= $form->field($model, 'body')->textarea(['rows' => 6, 'style' => 'resize: vertical']) ?>

            <?= !YII_ENV_TEST && !empty(Yii::$app->reCaptcha->secretV3)
                ? $form->field($model, 'reCaptcha')->label(false)->widget(ReCaptcha3::class)
                : null ?>

            <div class="form-group">
                <?= Html::submitButton(
                    Yii::t('app', 'Submit'),
                    ['class' => 'btn btn-primary', 'name' => 'contact-button']
                ) ?>
            </div>

            <?php ActiveForm::end() ?>
        </div>
    </div>

</div>

<?php

use admin\widgets\ckfinder\CKFinderInputFile;
use admin\widgets\dynamicForm\DynamicFormHelper;
use admin\widgets\dynamicForm\DynamicFormWidget;
use common\widgets\AppActiveForm;
use kartik\icons\Icon;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var $this            yii\web\View
 * @var $modelMessage    common\models\TelegramMessage
 * @var $modelsImages    common\models\TelegramMessageImage[]
 * @var $modelsButtons   common\models\TelegramMessageButton[]
 * @var $form            AppActiveForm
 * @var $isCreate        bool
 */
?>

<div class="telegram-message-form">

    <?php
    $form = AppActiveForm::begin() ?>

    <?= $form->field($modelMessage, 'text')->textarea(['rows' => 6]) ?>

    <?= $form->field($modelMessage, 'serial_number')->textInput() ?>

    <?= $form->field($modelMessage, 'key')->textInput(['maxlength' => true]) ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            DynamicFormWidget::begin([
                'widgetContainer' => 'image_dynamicform_wrapper',
                // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                'widgetBody' => '.container-images',
                // required: css class selector
                'widgetItem' => '.image',
                // required: css class.
                'limit' => 20,
                // the maximum times, an element can be cloned (default 999)
                'min' => 0,
                // 0 or 1 (default 1)
                'insertButton' => '.add-image',
                // css class
                'deleteButton' => '.remove-image',
                // css class
                'model' => $modelsImages[0],
                'formId' => $form->id,
                'formFields' => [
                    'image',
                    'serial_number'
                ],
            ]); ?>

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Images') ?></th>
                    <th class="text-center" style="width: 90px;">
                        <?= DynamicFormHelper::plusButton('add-image') ?>
                    </th>
                </tr>
                </thead>
                <tbody class="container-images">
                <?php
                foreach ($modelsImages as $i => $modelImage): ?>
                    <tr class="image">
                        <td class="-">
                            <?php
                            if (!$modelImage->isNewRecord) {
                                echo Html::activeHiddenInput($modelImage, "[$i]id");
                            }
                            ?>
                            <?= $form->field($modelImage, "[$i]image")->widget(
                                CKFinderInputFile::class
                            ) ?>
                            <?= $form->field($modelImage, "[$i]serial_number")->textInput() ?>
                        </td>
                        <td class="text-center v-center" style="width: 90px; verti">
                            <?= DynamicFormHelper::minusButton('remove-image') ?>
                        </td>
                    </tr>
                <?php
                endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th></th>
                    <th class="text-center" style="width: 90px;">
                        <?= DynamicFormHelper::plusButton('add-image') ?>
                    </th>
                </tr>
                </tfoot>
            </table>
            <?php
            DynamicFormWidget::end(); ?>
        </div>
    </div>

<!--    <br>-->
<!--    <div class="panel panel-default">-->
<!--        <div class="panel-body">-->
<!--            --><?php
//            DynamicFormWidget::begin([
//                'widgetContainer' => 'button_dynamicform_wrapper',
//                // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
//                'widgetBody' => '.container-buttons',
//                // required: css class selector
//                'widgetItem' => '.button',
//                // required: css class.
//                'limit' => 20,
//                // the maximum times, an element can be cloned (default 999)
//                'min' => 0,
//                // 0 or 1 (default 1)
//                'insertButton' => '.add-button',
//                // css class
//                'deleteButton' => '.remove-button',
//                // css class
//                'model' => $modelsButtons[0],
//                'formId' => $form->id,
//                'formFields' => [
//                    'btn_name',
//                    'text',
//                    'serial_number'
//                ],
//            ]); ?>
<!---->
<!--            <table class="table table-bordered table-striped">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th>--><?php //= Yii::t('app', 'Buttons') ?><!--</th>-->
<!--                    <th class="text-center" style="width: 90px;">-->
<!--                        --><?php //= DynamicFormHelper::plusButton('add-button') ?>
<!--                    </th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody class="container-buttons">-->
<!--                --><?php
//                foreach ($modelsButtons as $i => $modelButton): ?>
<!--                    <tr class="button">-->
<!--                        <td class="-">-->
<!--                            --><?php
//                            if (!$modelButton->isNewRecord) {
//                                echo Html::activeHiddenInput($modelButton, "[$i]id");
//                            }
//                            ?>
<!--                            --><?php //= $form->field($modelButton, "[$i]btn_name")->textInput() ?>
<!--                            --><?php //= $form->field($modelButton, "[$i]text")->widget(
//                                \admin\widgets\ckeditor\EditorClassic::class
//                            ) ?>
<!--                            --><?php //= $form->field($modelButton, "[$i]serial_number")->textInput() ?>
<!--                        </td>-->
<!--                        <td class="text-center v-center" style="width: 90px; verti">-->
<!--                            --><?php //= DynamicFormHelper::minusButton('remove-button') ?>
<!--                        </td>-->
<!--                    </tr>-->
<!--                --><?php
//                endforeach; ?>
<!--                </tbody>-->
<!--                <tfoot>-->
<!--                <tr>-->
<!--                    <th></th>-->
<!--                    <th class="text-center" style="width: 90px;">-->
<!--                        --><?php //= DynamicFormHelper::plusButton('add-button') ?>
<!--                    </th>-->
<!--                </tr>-->
<!--                </tfoot>-->
<!--            </table>-->
<!--            --><?php
//            DynamicFormWidget::end(); ?>
<!--        </div>-->
<!--    </div>-->

    <div class="form-group">
        <?php
        if ($isCreate) {
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Create New'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=create']
            );
            echo Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save And Return To List'),
                ['class' => 'btn btn-success', 'formaction' => Url::to() . '?redirect=index']
            );
        } ?>
        <?= Html::submitButton(Icon::show('save') . Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php
    AppActiveForm::end() ?>

</div>

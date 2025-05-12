<?php

use admin\modules\modelExportImport\models\ImportModel;
use yii\bootstrap5\{ActiveForm, Html};
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this   View
 * @var $action string
 */

if (!$action) {
    $action = Url::to(['import']);
}
$model = new ImportModel();

$form = ActiveForm::begin(['action' => $action]); ?>

<?= $form->field($model, 'file')->fileInput() ?>

<div class="form-group">
    <?= Html::submitButton('Импортировать', ['class' => 'btn btn-success']) ?>
</div>
<?php ActiveForm::end() ?>
<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\{gridView\Column, gridView\ColumnDate, gridView\ColumnSelect2};
use admin\models\UserAdmin;
use admin\widgets\sortableGridView\SortableGridView;
use common\components\export\ExportMenu;
use common\modules\log\{enums\LogOperation, enums\LogStatus, Log as LogModule, models\Log, widgets\ListColumn};
use kartik\grid\SerialColumn;
use yii\bootstrap5\Html;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\modules\log\models\LogSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t(LogModule::MODULE_MESSAGES, 'Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php
        $gridColumns = [
            ['class' => SerialColumn::class],
            'table_model',
            'record_id',
            'operation_type',
            'field',
            'before',
            'after',
            'time',
            'user_admin_id',
            'user_agent',
            'ip',
            'status',
            'description'
        ];
        // Renders an export dropdown menu
        echo ExportMenu::widget([
            'id' => 'log-export-menu',
            'staticConfig' => Log::class,
            'dataProvider' => $dataProvider,
            'filename' => 'export_logs_' . date('j.m.Y_G:i'),
            'batchSize' => 100
        ]) ?>
    </p>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'columns' => [
            ['class' => SerialColumn::class],

            ColumnSelect2::widget([
                'attr' => 'table_model',
                'items' => Log::getTableModels(),
                'editable' => false
            ]),
            ColumnSelect2::widget([
                'attr' => 'operation_type',
                'items' => LogOperation::class,
                'hideSearch' => true,
                'editable' => false
            ]),
            Column::widget(['attr' => 'record_id', 'editable' => false, 'width' => 20]),
            ListColumn::widget(['attr' => 'field']),
            ListColumn::widget(['attr' => 'before', 'width' => 330]),
            ListColumn::widget(['attr' => 'after', 'width' => 330]),
            ColumnDate::widget(['attr' => 'time', 'searchModel' => $searchModel, 'editable' => false]),
            ColumnSelect2::widget([
                'attr' => 'user_admin_id',
                'items' => UserAdmin::find()->select(['username AS name', 'id'])->indexBy('id')->column(),
                'editable' => false
            ]),
            Column::widget(['attr' => 'ip', 'editable' => false]),
            ColumnSelect2::widget(['attr' => 'status', 'items' => LogStatus::class, 'editable' => false]),

            ['class' => GroupedActionColumn::class, 'template' => '{view}']
        ]
    ]) ?>

</div>

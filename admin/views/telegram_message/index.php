<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\TelegramMessageSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\TelegramMessage
 */

$this->title = Yii::t('app', 'Telegram Messages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-message-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?= 
            RbacHtml::a(Yii::t('app', 'Create Telegram Message'), ['create'], ['class' => 'btn btn-success']);
//           $this->render('_create_modal', ['model' => $model]);
        ?>
    </div>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => SerialColumn::class],

            Column::widget(),
            Column::widget(['attr' => 'type']),
            Column::widget(['attr' => 'text', 'format' => 'ntext']),
            Column::widget(['attr' => 'serial_number']),
            Column::widget(['attr' => 'command_id']),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>

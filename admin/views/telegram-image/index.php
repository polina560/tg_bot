<?php

use admin\components\GroupedActionColumn;
use admin\components\widgets\gridView\Column;
use admin\modules\rbac\components\RbacHtml;
use admin\widgets\sortableGridView\SortableGridView;
use kartik\grid\SerialColumn;
use yii\widgets\ListView;

/**
 * @var $this         yii\web\View
 * @var $searchModel  common\models\TelegramImageSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        common\models\TelegramImage
 */

$this->title = Yii::t('app', 'Telegram Images');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-image-index">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <div>
        <?= 
            RbacHtml::a(Yii::t('app', 'Create Telegram Image'), ['create'], ['class' => 'btn btn-success']);
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
            Column::widget(['attr' => 'image']),
            Column::widget(['attr' => 'key']),
            Column::widget(['attr' => 'serial_number']),

            ['class' => GroupedActionColumn::class]
        ]
    ]) ?>
</div>

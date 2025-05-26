<?php

use common\components\helpers\UserUrl;
use common\models\TelegramImageSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\TelegramImage
 */

$this->title = Yii::t('app', 'Create Telegram Image');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Telegram Images'),
    'url' => UserUrl::setFilters(TelegramImageSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-image-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>

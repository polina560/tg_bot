<?php

use common\components\helpers\UserUrl;
use common\models\TelegramStateSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\TelegramState
 */

$this->title = Yii::t('app', 'Create Telegram State');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Telegram States'),
    'url' => UserUrl::setFilters(TelegramStateSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-state-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>

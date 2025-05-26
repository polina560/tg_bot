<?php

use common\components\helpers\UserUrl;
use common\models\TelegramButtonSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $model common\models\TelegramButton
 */

$this->title = Yii::t('app', 'Create Telegram Button');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Telegram Buttons'),
    'url' => UserUrl::setFilters(TelegramButtonSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-button-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'isCreate' => true]) ?>

</div>

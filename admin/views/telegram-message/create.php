<?php

use common\components\helpers\UserUrl;
use common\models\TelegramMessageSearch;
use yii\bootstrap5\Html;

/**
 * @var $this  yii\web\View
 * @var $modelMessage common\models\TelegramMessage
 * @var $modelsImages common\models\TelegramMessageImage[]
 * @var $modelsButtons common\models\TelegramMessageButton[]
 */

$this->title = Yii::t('app', 'Create Telegram Message');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Telegram Messages'),
    'url' => UserUrl::setFilters(TelegramMessageSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-message-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'modelMessage' => $modelMessage,
        'modelsImages' => $modelsImages,
        'modelsButtons' => $modelsButtons,
        'isCreate' => true
    ]) ?>

</div>

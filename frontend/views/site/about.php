<?php

use yii\bootstrap5\Html;

/**
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', 'About');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('about.pug', ['title' => Html::encode($this->title)]) ?>

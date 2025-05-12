<?php

/**
 * @var $this yii\web\View
 */

$this->title = Yii::$app->name;
?>
<div class="site-index">
    <main-jumbotron
        title="<img src='images/logos/yii.svg' height='100' alt='Yii2 Framework'/>+<img src='images/logos/Vue.js_Logo_2.svg' height='100' alt='VueJS 3'/><br>Добро пожаловать!"
        lead="Вы успешно развернули Yii2 шаблон с VueJs."
    >
        <?php if (!file_exists(Yii::getAlias('@vue/dist/.vite/manifest.json'))): ?>
            <div class="alert alert-danger" role="alert">
                VueJs не установлен! Выполните команды <code>pnpm install</code> и <code>pnpm build</code>
            </div>
        <?php endif ?>
    </main-jumbotron>
    <?= $this->render('index.pug') ?>
</div>

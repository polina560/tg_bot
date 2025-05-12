<?php

use common\widgets\Alert;
use common\widgets\VueIcon;
use frontend\assets\{FrontendAsset, VueAsset};
use yii\bootstrap5\{Breadcrumbs, Html, Nav, NavBar};
use yii\helpers\Url;

/**
 * @var $this    yii\web\View
 * @var $content string
 */

FrontendAsset::register($this);
VueAsset::register($this);
$containerClass = (array_key_exists('layout_class', $this->params) && $this->params['layout_class'])
    ? $this->params['layout_class']
    : 'container';
$breadcrumbStyle = str_contains($containerClass, 'container-fluid') ? ' style="padding-top: 20px"' : '';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
        $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div id="app">
        <div class="wrap">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar navbar-dark bg-dark navbar-fixed-top navbar-expand-lg',
                ]
            ]);
            $menuItems = [
                [
                    'label' => VueIcon::widget(['icon' => 'home']) . "&nbsp;" . Yii::t('app', 'Home'),
                    'url' => ['/site/index']
                ],
                [
                    'label' => VueIcon::widget(['icon' => 'info']) . "&nbsp;" . Yii::t('app', 'About'),
                    'url' => ['/site/about']
                ],
                [
                    'label' => VueIcon::widget(['icon' => 'envelope']) . "&nbsp;" . Yii::t('app', 'Contact'),
                    'url' => ['/site/contact']
                ]
            ];
            if (Yii::$app->user->isGuest) {
                if (Yii::$app->params['signup']['enabled_clients']['email-password']) {
                    $menuItems[] = [
                        'label' => VueIcon::widget(['icon' => 'user-plus']) . "&nbsp;" . Yii::t('app', 'Signup'),
                        'url' => ['/site/signup']
                    ];
                }
                $menuItems[] = [
                    'label' => VueIcon::widget(['icon' => 'sign-in-alt']) . "&nbsp;" . Yii::t('app', 'Login'),
                    'url' => ['/site/login']
                ];
            } else {
                $menuItems[] = Html::tag(
                    'li',
                    Html::a(
                        sprintf(
                            '%s&nbsp;%s%s) ',
                            VueIcon::widget(['icon' => 'sign-out-alt']),
                            Yii::t('app', 'Logout ('),
                            Yii::$app->user->identity->username
                        ),
                        ['/site/logout'],
                        ['class' => 'nav-link', 'data-method' => 'POST']
                    ),
                    ['class' => 'nav-item']
                );
            }
            echo Nav::widget([
                'options' => ['class' => 'nav navbar-nav ms-auto d-flex nav-pills justify-content-between'],
                'items' => $menuItems,
                'encodeLabels' => false,
                'activateParents' => true
            ]);
            NavBar::end(); ?>
            <div class="<?= $containerClass ?>">
                <nav aria-label="breadcrumb" class="mb-3" <?= $breadcrumbStyle ?>>
                    <?= Breadcrumbs::widget([
                        'homeLink' => ['label' => Yii::t('yii', 'Home'), 'url' => Url::to(['/'])],
                        'links' => $this->params['breadcrumbs'] ?? [],
                        'options' => ['class' => 'breadcrumb'],
                        'tag' => 'ol',
                        'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                        'activeItemTemplate' => '<li class="breadcrumb-item active" aria-current="page">{link}</li>'
                    ]) ?>
                </nav>
                <?= Alert::widget(['options' => ['class' => 'alert show', 'role' => 'alert']]) ?>
                <?= $content ?>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            </div>
        </footer>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>
<?php

use common\assets\scssConverter\ScssAssetConverter;
use common\components\{Environment, ErrorHandler, helpers\ModuleHelper, Request, UserUrlManager, UserView};
use common\modules\user\models\User;
use ScssPhp\ScssPhp\{Compiler as ScssCompiler, OutputStyle as ScssOutputStyle};
use yii\redis\Session as RedisSession;
use yii\web\Session;

$basePath = Environment::readEnv('BASE_URI') ?: '/';

$params = array_merge(
    require dirname(__DIR__, 2) . '/common/config/params.php',
    require dirname(__DIR__, 2) . '/common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => ModuleHelper::FRONTEND,
    'name' => 'PROJECT NAME',
    'homeUrl' => $basePath,
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'controllerNamespace' => 'frontend\controllers',
    'aliases' => [

    ],
    'modules' => [

    ],
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
            'converter' => ScssAssetConverter::class
        ],

        'request' => [
            'class' => Request::class,
            'csrfParam' => '_csrf-frontend',
            'scriptUrl' => $basePath,
            'baseUrl' => rtrim($basePath, '/'),
            'csrfCookie' => ['httpOnly' => true, 'path' => $basePath]
        ],

        'user' => [
            'identityClass' => User::class,
            'enableSession' => true,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true, 'path' => $basePath]
        ],

        'session' => [
            'class' => !empty(Environment::readEnv('REDIS_HOSTNAME'))
            && !empty(Environment::readEnv('REDIS_PORT'))
                ? RedisSession::class
                : Session::class,
            'name' => 'advanced-frontend',
            'cookieParams' => ['httpOnly' => true, 'path' => $basePath]
        ],

        'errorHandler' => [
            'class' => ErrorHandler::class,
            'errorAction' => 'site/error'
        ],

        'view' => [
            'class' => UserView::class
        ],

        'urlManager' => [
            'class' => UserUrlManager::class,
            'hideIndex' => true,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
                '<action:[\-\w]+>' => 'site/<action>',
                '<controller:[\-\w]+>' => '<controller>/index',
                '<controller:[\-\w]+>/<action:[\-\w]+>' => '<controller>/<action>'
            ]
        ]
    ],
    'container' => [
        'definitions' => [
            ScssCompiler::class => static function () {
                $compiler = new ScssCompiler();
                if (!YII_ENV_DEV) {
                    $compiler->setOutputStyle(ScssOutputStyle::COMPRESSED);
                }
                return $compiler;
            }
        ]
    ],
    'params' => $params
];

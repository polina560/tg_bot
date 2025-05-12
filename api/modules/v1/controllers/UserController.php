<?php

namespace api\modules\v1\controllers;

use common\components\exceptions\ModelSaveException;
use common\modules\user\{actions\EmailConfirmAction,
    actions\EmailConfirmSendAction,
    actions\LoginAction,
    actions\LogoutAction,
    actions\PasswordResetAction,
    actions\PasswordRestoreAction,
    actions\ProfileAction,
    actions\ServiceDataGetAction,
    actions\ServiceDataSaveAction,
    actions\SignupAction,
    actions\UpdateAction,
    helpers\UserHelper,
    Module
};
use yii\authclient\{AuthAction, ClientInterface};
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class UserController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserController extends AppController
{
    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        Module::initI18N();
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'auth' => ['except' => ['auth', 'login', 'signup', 'password-restore', 'password-reset', 'email-confirm']],
            'requestLimiter' => [
                'blockingLimits' => [
                    'login' => [
                        [
                            'max_errors' => 3, // Кол-во допустимых ошибок за
                            'period' => 15, // период в котором ожидаются неверные значения
                            'cooldown' => 60
                        ],
                        [
                            'max_errors' => 3, // Кол-во допустимых ошибок за
                            'period' => 15, // период в котором ожидаются неверные значения
                            'cooldown' => 60 * 30
                        ],
                        [
                            'max_errors' => 3, // Кол-во допустимых ошибок за
                            'period' => 15, // период в котором ожидаются неверные значения
                            'cooldown' => 60 * 3600 * 24
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
                'successUrl' => '/',
                'defaultClientId' => 'vk'
            ],
            'signup' => SignupAction::class,
            'login' => LoginAction::class,
            'logout' => LogoutAction::class,
            'update' => UpdateAction::class,
            'profile' => ProfileAction::class,
            'email-confirm-send' => EmailConfirmSendAction::class,
            'email-confirm' => EmailConfirmAction::class,
            'password-restore' => PasswordRestoreAction::class,
            'password-reset' => PasswordResetAction::class,
            'service-data-save' => ServiceDataSaveAction::class,
            'service-data-get' => ServiceDataGetAction::class,
        ];
    }

    /**
     * @throws Exception
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public function onAuthSuccess(ClientInterface $client): void
    {
        UserHelper::handleAuth($client);
    }
}

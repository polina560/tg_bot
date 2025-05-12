<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\components\exceptions\ModelSaveException;
use common\modules\user\{helpers\UserHelper, models\LoginForm};
use OpenApi\Attributes\{Items, Post, Property};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\HttpException;

/**
 * Авторизация пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/login',
    operationId: 'login',
    description: 'Авторизация с помощью логина + пароль',
    summary: 'Авторизация',
    tags: ['user']
)]
#[RequestFormData(
    requiredProps: ['login', 'password'],
    properties: [
        new Property(property: 'login', description: 'Имя пользователя или E-mail адрес', type: 'string'),
        new Property(property: 'password', description: 'Пароль', type: 'string')
    ]
)]
#[JsonSuccess(content: [new Property(property: 'profile', ref: '#/components/schemas/Profile')])]
#[JsonError(description: 'Login error',
    content: [
        new Property(
            property: 'login', type: 'array',
            items: new Items(type: 'string', example: 'Необходимо заполнить «Логин».')
        ),
        new Property(
            property: 'password', type: 'array',
            items: new Items(type: 'string', example: 'Неверный логин или пароль')
        )
    ]
)]
class LoginAction extends BaseAction
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ModelSaveException
     * @throws HttpException
     */
    final public function run(): array
    {
        $form = new LoginForm();
        $form->load(Yii::$app->request->post(), '');
        if (!$form->login()) {
            $this->controller->addWrongValue(Yii::$app->request->post('login') . ':' . Yii::$app->request->post('password'));
            return $this->controller->returnError('Login error', $form->errors);
        }
        return $this->controller->returnSuccess(UserHelper::getProfile($form->user), 'profile');
    }
}

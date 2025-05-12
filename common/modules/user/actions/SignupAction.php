<?php

namespace common\modules\user\actions;

use api\behaviors\returnStatusBehavior\{JsonError, JsonSuccess, RequestFormData};
use common\components\exceptions\ModelSaveException;
use common\modules\user\{helpers\UserHelper, models\SignupForm, Module};
use OpenApi\Attributes\{Items, Post, Property};
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\web\HttpException;

/**
 * Регистрация пользователя
 *
 * @package user\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
#[Post(
    path: '/user/signup',
    operationId: 'signup',
    description: 'Регистрация нового пользователя',
    summary: 'Регистрация',
    tags: ['user']
)]
#[RequestFormData(
    requiredProps: ['username', 'email', 'password'],
    properties: [
        new Property(property: 'username', description: 'Никнейм', type: 'string'),
        new Property(property: 'email', description: 'E-mail адрес', type: 'string'),
        new Property(property: 'password', description: 'Пароль', type: 'string'),
        new Property(
            property: 'rules_accepted', description: 'Согласие с правилами: 0 - отказ, 1 - принял', type: 'integer'
        )
    ]
)]
#[JsonSuccess(content: [new Property(property: 'profile', ref: '#/components/schemas/Profile')])]
#[JsonError(
    description: 'Validation error',
    content: [
        new Property(
            property: 'username', type: 'array',
            items:    new Items(type: 'string', example: 'Пользователь с таким логином уже зарегистрирован')
        ),
        new Property(
            property: 'email', type: 'array',
            items:    new Items(type: 'string', example: 'Такой Email уже зарегистрирован')
        ),
        new Property(
            property: 'rules_accepted', type: 'array',
            items:    new Items(type: 'string', example: 'Необходимо согласиться с правилами')
        )
    ]
)]
class SignupAction extends BaseAction
{
    /**
     * @throws Exception
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws ModelSaveException
     */
    final public function run(): array
    {
        Module::initI18N();
        if (!Yii::$app->params['signup']['enabled_clients']['email-password']) {
            return $this->controller->returnError(Yii::t(Module::MODULE_ERROR_MESSAGES, 'Registration disabled'));
        }
        $form = new SignupForm();
        $form->load(Yii::$app->request->post(), '');
        if (!$user = $form->signup()) {
            return $this->controller->returnError('Validation error', $form->errors);
        }

        return $this->controller->returnSuccess(UserHelper::getProfile($user), 'profile');
    }
}

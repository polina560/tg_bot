<?php

namespace api\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\IdentityInterface;

class SessionAuth extends AuthMethod
{
    /**
     * @inheritDoc
     */
    public function authenticate($user, $request, $response): IdentityInterface|null
    {
        return Yii::$app->user->identity;
    }
}

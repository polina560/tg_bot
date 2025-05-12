<?php

namespace common\modules\user\models;

use common\components\exceptions\ModelSaveException;
use common\modules\user\helpers\UserHelper;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\HttpException;

/**
 * Реализация IdentityInterface через UserAgent модель
 *
 * Позволяет авторизовать пользователя по UserAgent заголовку на разных устройствах по отдельности
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait UserAgentIdentity
{
    /**
     * {@inheritdoc}
     *
     * @throws HttpException
     */
    public static function findIdentityByAccessToken($token, $type = null): ?User
    {
        $user_agent = Yii::$app->request->shortUserAgent;
        if (!empty($token) && $userAgent = UserAgent::findOne(['auth_key' => $token, 'value' => $user_agent])) {
            $user = self::findOne([$userAgent->user_id]);
            return UserHelper::checkUserStatus($user);
        }
        return null;
    }

    /**
     * Logout
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    public static function logout(): bool
    {
        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            Yii::$app->user->logout();
            if (
                $user_key = UserAgent::findOne(['user_id' => $user->id, 'value' => Yii::$app->request->shortUserAgent])
            ) {
                $user_key->delete();
            }
            return $user->save();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAuthKey(): ?string
    {
        return UserAgent::getAuthKey($this->id);
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws Exception
     */
    final public function generateAuthKey(): void
    {
        $shortUserAgent = Yii::$app->request->shortUserAgent;
        if (!$userAgent = UserAgent::findOne(['user_id' => $this->id, 'value' => $shortUserAgent])) {
            $userAgent = new UserAgent();
            $userAgent->user_id = $this->id;
            $userAgent->value = $shortUserAgent;
        }
        $userAgent->auth_key = Yii::$app->security->generateRandomString();
        $userAgent->save(false);
    }
}

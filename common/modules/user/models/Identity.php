<?php

namespace common\modules\user\models;

use common\components\exceptions\ModelSaveException;
use common\modules\user\{enums\Status, helpers\UserHelper};
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\HttpException;

/**
 * Базовая реализация IdentityInterface
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait Identity
{
    /**
     * Get user by username
     *
     * @throws HttpException
     */
    public static function findIdentityByUsername(?string $username): ?self
    {
        if (empty($username)) {
            return null;
        }
        $user = self::findOne(['username' => $username]);
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Get user by email
     */
    public static function findIdentityByEmail(?string $email): ?User
    {
        if (empty($email)) {
            return null;
        }
        $user = null;
        if ($emailModel = Email::findOne(['value' => $email])) {
            $user = $emailModel->user;
        }
        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @throws HttpException
     */
    public static function findIdentityByAccessToken($token, $type = null): ?User
    {
        /** @var self|null $user */
        if (!empty($token)
            && $user = self::find()
                ->where(['auth_key' => $token])
                ->andWhere(['IS NOT', 'auth_key', null]) // Проверка на null, т.к. в БД может храниться null
                ->one()) {
            return UserHelper::checkUserStatus($user);
        }
        return null;
    }

    /**
     * Find identity
     *
     * @throws HttpException
     */
    public static function findIdentity($id, bool $with_ext = false): ?self
    {
        $user = self::findOne($id);
        /** @var User $user */
        return UserHelper::checkUserStatus($user);
    }

    /**
     * Logout
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function logout(): bool
    {
        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            Yii::$app->user->logout();
            $user->auth_key = null;
            return $user->save();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    final public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    final public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritdoc}
     */
    final public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    /**
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    final public function login(string $authSource = User::AUTH_SOURCE_EMAIL, int $duration = 0): bool
    {
        UserHelper::checkUserStatus($this);
        if ($this->getAuthKey() === null) {
            $this->generateAuthKey();
        }
        $this->auth_source = $authSource;
        $this->last_login_at = time();
        $this->last_ip = Yii::$app->request->longUserIp;
        if ($this->save(false)) {
            return Yii::$app->user->login($this, $duration);
        }
        return false;
    }

    /**
     * Generates "remember me" authentication key
     *
     * @throws Exception
     */
    final public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->save(false);
    }

    /**
     * Блокировка пользователя
     *
     * @throws \yii\db\Exception
     */
    final public function ban(): void
    {
        $this->status = Status::Blocked->value;
        $this->save(false);
    }
}

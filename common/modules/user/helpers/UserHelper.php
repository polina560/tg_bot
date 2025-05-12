<?php

namespace common\modules\user\helpers;

use common\components\{exceptions\ModelSaveException, Faker};
use common\enums\Boolean;
use common\modules\user\{enums\Status,
    models\Auth,
    models\Email,
    models\User,
    models\UserAgent,
    models\UserExt,
    Module};
use Exception;
use Yii;
use yii\authclient\ClientInterface;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\{HttpException, IdentityInterface};

/**
 * Class UserHelper
 *
 * @package user
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserHelper
{
    /**
     * Получаем данные пользователя
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws HttpException
     */
    public static function getProfile(?User $user = null): array
    {
        $profile = [];
        if (!$user) {
            if (!Yii::$app->user->isGuest) {
                /** @var User $identity */
                $identity = Yii::$app->user->identity;
                $ip = Yii::$app->request->longUserIp;
                if ($ip !== $identity->last_ip) {
                    $identity->last_ip = $ip;
                    if (!$identity->save()) {
                        throw new ModelSaveException($identity);
                    }
                }
                $profile = $identity->profile;
            }
        } else {
            if (!$user->authKey) {
                $user->generateAuthKey();
            }
            $profile = $user->profile;
        }
        return $profile;
    }

    /**
     * Создание пользователя из e-mail.
     *
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public static function createNewUser(
        string $username = null,
        string $password = null,
        string $authSource = User::AUTH_SOURCE_EMAIL,
    ): User {
        $user = new User();
        $user->setPassword($password ?: Yii::$app->security->generateRandomString());
        $user->username = $username;
        if (!$user->username) {
            $user->generateUsername();
        }
        $user->auth_source = $authSource;
        $user->status = Status::Active->value;
        $user->last_login_at = time();
        if (!$user->save()) {
            throw new ModelSaveException($user);
        }
        $user->refresh();
        $user->generateAuthKey();
        return $user;
    }

    /**
     * Создание e-mail'а пользователя
     *
     * @throws \yii\base\Exception
     */
    public static function createUserEmail(
        ActiveRecord|IdentityInterface|User $user,
        string $email,
        bool $verify = false,
        bool $send = true,
    ): Email {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$user_email = Email::findOne(['user_id' => $user->id])) {
            $user_email = new Email();
            $user_email->user_id = $user->id;
        }
        $user_email->value = $email;
        $user_email->is_confirmed = Boolean::No->value;

        //Если подтверждение почты не требуется, сразу считаем подтверждённой
        if (
            !$userModule->enableEmailVerification ||
            ($userModule->autoVerifyEmailFromSocNet === true && $verify === true)
        ) {
            $user_email->is_confirmed = Boolean::Yes->value;
        } elseif ($send === true && $userModule->autoSendVerificationEmail === true) { //Если включена авто рассылка писем подтверждения
            $user_email->sendVerificationEmail();
        }

        if (!$user_email->save()) {
            throw new ModelSaveException($user_email);
        }
        $user->populateRelation('email', $user_email);
        return $user_email;
    }

    /**
     * Создание записи в UserExt
     *
     * @throws ModelSaveException
     * @throws \yii\db\Exception
     */
    public static function createUserExt(
        ActiveRecord|IdentityInterface|User $user,
        Boolean $rules_accepted = Boolean::No,
    ): UserExt {
        $userExt = new UserExt();
        $userExt->user_id = $user->id;
        $userExt->rules_accepted = $rules_accepted->value;
        if (!$userExt->save()) {
            throw new ModelSaveException($userExt);
        }
        return $userExt;
    }


    /**
     * Проверка статуса пользователя
     *
     * @throws HttpException 403 ошибка, если пользователь заблокирован
     */
    public static function checkUserStatus(?User $user): ?User
    {
        if ($user && ($user->status !== Status::Active->value)) {
            throw new HttpException(403, Yii::t(Module::MODULE_MESSAGES, 'User is Blocked'));
        }
        return $user;
    }

    /**
     * Блокировка текущего пользователя
     *
     * @throws ModelSaveException
     */
    public static function blockCurrentUser(): void
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $user->ban();
    }

    /**
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     */
    public static function createFake(int $count = 1): array
    {
        $faker = new Faker();
        $users = $faker->fill(User::class, $count, [
            'rules' => [
                'auth_source' => 'fake',
                'password_reset_token' => Yii::$app->security->generateRandomString(),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'status' => 0,
                'last_ip' => Yii::$app->request->longUserIp,
            ],
        ]);
        foreach ($users as $user) {
            $faker->fill(UserExt::class, 1, [
                'rules' => [
                    'rules_accepted' => Boolean::Yes->value,
                    'service_data' => '{}',
                ],
                User::class => $user,
            ]);
            $faker->fill(Email::class, 1, [
                'rules' => [
                    'value' => $faker->generator->email,
                    'confirm_token' => Yii::$app->security->generateRandomString() . '_' . time(),
                    'is_confirmed' => Boolean::No->value,
                ],
                User::class => $user,
            ]);
            $faker->fill(UserAgent::class, 1, [
                'rules' => [
                    'auth_key' => Yii::$app->security->generateRandomString(),
                    'value' => Yii::$app->request->shortUserAgent,
                ],
                User::class => $user,
            ]);
            $user->refresh();
        }
        return $users;
    }

    /**
     * @throws \yii\db\Exception
     * @throws ModelSaveException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public static function handleAuth(ClientInterface $client): void
    {
        $attributes = $client->getUserAttributes();
        $id = ArrayHelper::getValue($attributes, 'id')
            ?: ArrayHelper::getValue($attributes, 'user_id')
                ?: ArrayHelper::getValue($attributes, 'sub');

        $login = ArrayHelper::getValue($attributes, 'screen_name')
            ?: ArrayHelper::getValue($attributes, 'nickname')
                ?: (ArrayHelper::getValue($attributes, 'first_name') . ' ' .
                    ArrayHelper::getValue($attributes, 'last_name'));

        /* @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $id,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                self::updateUserInfo($user, $client);
                Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
            } else { // signup
                if ($login !== null && User::find()->where(['username' => $login])->exists()) {
                    Yii::$app->session->setFlash('error', [
                        Yii::t(
                            'app',
                            'User with the same email as in {client} account already exists but isn\'t linked to it. Login using email first to link it.',
                            ['client' => $client->getTitle()],
                        ),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(21);
                    $transaction = User::getDb()->beginTransaction();
                    $user = self::createNewUser($login, $password, $client->getId());
                    self::createUserExt($user);
                    self::updateUserInfo($user, $client);
                    $auth = new Auth([
                        'user_id' => $user->id,
                        'source' => $client->getId(),
                        'source_id' => (string)$id,
                    ]);
                    if ($auth->save()) {
                        $transaction->commit();
                        Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
                    } else {
                        throw new ModelSaveException($auth);
                    }
                }
            }
        } else {
            if ($auth && $auth->user_id !== Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', [
                    Yii::t(
                        'app',
                        'Unable to link {client} account. There is another user using it.',
                        ['client' => $client->getTitle()],
                    ),
                ]);
            } else {
                if (!$auth) { // add auth provider
                    $auth = new Auth([
                        'user_id' => Yii::$app->user->id,
                        'source' => $client->getId(),
                        'source_id' => (string)$attributes['id'],
                    ]);
                    if (!$auth->save()) {
                        throw new ModelSaveException($auth);
                    }
                }
                $user = $auth->user;
                self::updateUserInfo($user, $client);
                Yii::$app->session->setFlash('success', [
                    Yii::t('app', 'Linked {client} account.', [
                        'client' => $client->getTitle(),
                    ]),
                ]);
            }
        }
    }

    /**
     * @throws \yii\db\Exception
     * @throws Exception
     */
    private static function updateUserInfo(User $user, ClientInterface $client): void
    {
        $attributes = $client->getUserAttributes();
        $given_name = ArrayHelper::getValue($attributes, 'given_name')
            ?: ArrayHelper::getValue($attributes, 'first_name');
        $family_name = ArrayHelper::getValue($attributes, 'family_name')
            ?: ArrayHelper::getValue($attributes, 'last_name');
        if (!$user->userExt->first_name && $given_name) {
            $user->userExt->first_name = $given_name;
            $user->userExt->save(false);
        }
        if (!$user->userExt->last_name && $family_name) {
            $user->userExt->last_name = $family_name;
            $user->userExt->save(false);
        }
    }
}

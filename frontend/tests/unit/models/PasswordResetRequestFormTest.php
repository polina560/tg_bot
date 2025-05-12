<?php

namespace frontend\tests\unit\models;

use Codeception\Test\Unit;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use frontend\models\PasswordResetRequestForm;
use frontend\tests\UnitTester;

/**
 * Class PasswordResetRequestFormTest
 * @package frontend\tests\unit\models
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class PasswordResetRequestFormTest extends Unit
{
    protected UnitTester $tester;

    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
            'user_ext' => [
                'class' => UserExtFixture::class,
            ],
            'user_agent' => [
                'class' => UserAgentFixture::class,
            ],
            'email' => [
                'class' => EmailFixture::class,
                'dataFile' => codecept_data_dir() . 'email.php'
            ],
        ];
    }

    /**
     * @throws \JsonException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function testSendMessageWithWrongEmailAddress(): void
    {
        $model = new PasswordResetRequestForm();
        $model->email = 'not-existing-email@example.com';
        expect($model->sendEmail())->toBeFalse();
    }

//    /**
//     * @throws \yii\base\Exception
//     * @throws \yii\base\InvalidConfigException
//     */
//    public function testNotSendEmailsToInactiveUser(): void
//    {
//        $user = $this->tester->grabFixture('user', 1);
//        $model = new PasswordResetRequestForm();
//        $model->email = $user['email'];
//        expect_not($model->sendEmail());
//    }

//    /**
//     * @throws \yii\base\Exception
//     * @throws \yii\base\InvalidConfigException
//     */
//    public function testSendEmailSuccessfully(): void
//    {
//        $userFixture = $this->tester->grabFixture('user', 0);
//
//        $model = new PasswordResetRequestForm();
//        $model->email = $userFixture['email'];
//        $user = User::findOne(['password_reset_token' => $userFixture['password_reset_token']]);
//
//        expect_that($model->sendEmail());
//        expect_that($user->password_reset_token);
//
//        $emailMessage = $this->tester->grabLastSentEmail();
//        expect('valid email is sent', $emailMessage)->isInstanceOf(MessageInterface::class);
//        expect($emailMessage->getTo())->hasKey($model->email);
//        expect($emailMessage->getFrom())->hasKey(Yii::$app->params['supportEmail']);
//    }
}

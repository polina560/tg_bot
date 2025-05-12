<?php

namespace frontend\tests\unit\models;

use Codeception\Exception\ModuleException;
use Codeception\Test\Unit;
use common\modules\user\models\User;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use frontend\models\ResetPasswordForm;
use frontend\tests\UnitTester;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\HttpException;

/**
 * Class ResetPasswordFormTest
 * @package frontend\tests\unit\models
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ResetPasswordFormTest extends Unit
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

    public function testResetWrongToken(): void
    {
        $this->tester->expectThrowable(
            InvalidArgumentException::class,
            static function() {
                new ResetPasswordForm('');
            }
        );

        $this->tester->expectThrowable(
            InvalidArgumentException::class,
            static function() {
                new ResetPasswordForm('notexistingtoken_1391882543');
            }
        );
    }

    /**
     * @throws Exception
     * @throws HttpException
     */
    public function testResetCorrectToken(): void
    {
        $user = User::findIdentityByUsername('KropMih');
        $form = new ResetPasswordForm($user['password_reset_token']);
        $form->password = '111111';
        expect($form->resetPassword())->toBeTrue();
    }
}

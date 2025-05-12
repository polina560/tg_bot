<?php

namespace frontend\tests\functional;

use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use frontend\tests\FunctionalTester;

/**
 * Class LoginCest
 * @package frontend\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class LoginCest
{
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
            'user_ext' => [
                'class' => UserExtFixture::class,
                'dataFile' => codecept_data_dir() . 'user_ext.php'
            ],
            'user_agent' => [
                'class' => UserAgentFixture::class,
                'dataFile' => codecept_data_dir() . 'user_agent.php'
            ],
            'email' => [
                'class' => EmailFixture::class,
                'dataFile' => codecept_data_dir() . 'email.php'
            ]
        ];
    }

    public function incorrectLogin(FunctionalTester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Логин');
        $I->see('Пароль');
        $I->see('Запомнить');
        $I->fillField('Логин', 'KropMih51');
        $I->fillField('Пароль', 'incorrectPassword');
        $I->click('login-button');
        $I->wait(1);
        $I->seeValidationError('Неверный логин или пароль');
    }

    public function correctLogin(FunctionalTester $I): void
    {
        $I->amOnPage('/login');
        $I->see('Логин');
        $I->see('Пароль');
        $I->see('Запомнить');
        $I->fillField('Логин', 'KropMih');
        $I->fillField('Пароль', 'password_0');
        $I->click('login-button');
        $I->wait(1);
        $I->dontSeeValidationError('Неверный логин или пароль');
    }
}

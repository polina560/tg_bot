<?php

namespace frontend\tests\functional;

use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use frontend\tests\FunctionalTester;

/**
 * Class SignupCest
 * @package frontend\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SignupCest
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

    public function incorrectSignup(FunctionalTester $I): void
    {
        $I->amOnPage('signup');
        $I->see('Логин');
        $I->see('Email');
        $I->see('Пароль');
        $I->see('Согласен с правилами');
        $I->amGoingTo('try signup with duplicate data');
        $I->fillField('Логин', 'KropMih');
        $I->fillField('Email', 'krop5111@gmail.com');
        $I->fillField('Пароль', 'password_1');
        $I->checkOption('Согласен с правилами');
        $I->click('signup-button');
        $I->wait(1);
        $I->seeValidationError('Пользователь с таким логином уже зарегистрирован');
        $I->seeValidationError('Такой Email уже зарегистрирован');
    }

    public function correctSignup(FunctionalTester $I): void
    {
        $I->amOnPage('signup');
        $I->see('Логин');
        $I->see('Email');
        $I->see('Пароль');
        $I->see('Согласен с правилами');
        $I->amGoingTo('try signup with correct data');
        $I->fillField('Логин', 'KropMih51');
        $I->fillField('Email', 'krop2111@gmail.com');
        $I->fillField('Пароль', 'password_1');
        $I->checkOption('Согласен с правилами');
        $I->click('signup-button');
        $I->wait(1);
        $I->see('Спасибо за регистрацию. Пожалуйста, проверьте свои входящие сообщения и подтвердите email.');
    }
}
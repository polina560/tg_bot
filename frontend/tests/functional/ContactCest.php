<?php

namespace frontend\tests\functional;

use Codeception\Scenario;
use frontend\tests\FunctionalTester;

/**
 * Class ContactCest
 * @package frontend\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @var $scenario Scenario
 */
class ContactCest
{
    public function _before(FunctionalTester $I): void
    {
        $I->amOnPage('site/contact');
    }

    public function checkContact(FunctionalTester $I): void
    {
        $I->see('Связь', 'h1');
    }

    public function checkContactSubmitNoData(FunctionalTester $I): void
    {
        $I->submitForm('#contact-form', []);
        $I->wait(1);
        $I->see('Связь', 'h1');
        $I->seeValidationError('Необходимо заполнить «Название».');
        $I->seeValidationError('Необходимо заполнить «Email».');
        $I->seeValidationError('Необходимо заполнить «Тема».');
        $I->seeValidationError('Необходимо заполнить «Текст».');
    }

    public function checkContactSubmitNotCorrectEmail(FunctionalTester $I): void
    {
        $I->submitForm(
            '#contact-form',
            [
                'ContactForm[name]' => 'tester',
                'ContactForm[email]' => 'tester.email',
                'ContactForm[subject]' => 'test subject',
                'ContactForm[body]' => 'test content'
            ]
        );
        $I->wait(1);
        $I->seeValidationError('Значение «Email» не является правильным email адресом.');
        $I->dontSeeValidationError('Необходимо заполнить «Название».');
        $I->dontSeeValidationError('Необходимо заполнить «Тема».');
        $I->dontSeeValidationError('Необходимо заполнить «Текст».');
    }

    public function checkContactSubmitCorrectData(FunctionalTester $I): void
    {
        $I->submitForm(
            '#contact-form',
            [
                'ContactForm[name]' => 'tester',
                'ContactForm[email]' => 'tester@example.com',
                'ContactForm[subject]' => 'test subject',
                'ContactForm[body]' => 'test content'
            ]
        );
        $I->wait(1);
        $I->see('Спасибо за обращение. Мы ответим вам как можно скорее.');
    }
}

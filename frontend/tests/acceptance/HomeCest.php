<?php

namespace frontend\tests\acceptance;

use frontend\tests\AcceptanceTester;
use yii\helpers\Url;

/**
 * Class HomeCest
 * @package frontend\tests\acceptance
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class HomeCest
{
    public function checkHome(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Вы успешно развернули Yii2 шаблон');
        $I->amOnPage('about');
        $I->see('This is the About page.');
    }
}

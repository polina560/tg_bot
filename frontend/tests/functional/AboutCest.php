<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

/**
 * Class AboutCest
 * @package frontend\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AboutCest
{
    public function checkAbout(FunctionalTester $I): void
    {
        $I->amOnPage('about');
        $I->see('О нас', 'h1');
    }
}

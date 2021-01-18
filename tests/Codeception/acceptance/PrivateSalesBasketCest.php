<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Codeception;

use OxidEsales\Codeception\Module\Translation\Translator;

final class PrivateSalesBasketCest
{
    public function testIfblBasketExcludeEnabledBlocksRootCatChange(AcceptanceTester $I)
    {
        $I->wantToTest('Test if blBasketExcludeEnabled blocks rootCatChange and continue shopping clears basket.');

        $I->updateConfigInDatabase('blBasketExcludeEnabled', 'true', 'bool');
        $I->clearShopCache();

        $homePage = $I->openShop();

        $basketPage = $homePage->openCategoryPage('Test category 0 [EN] šÄßüл')
            ->openDetailsPage(1)
            ->addProductToBasket(1)
            ->openBasket();
        
        $productData = [
            'id' => '1000',
            'title' => 'Test product 0 [EN] šÄßüл',
            'amount' => 1,
            'totalPrice' => '50,00 €'
        ];

        $basketPage->seeBasketContains([$productData], '50,00 €');
        
        $homePage->openCategoryPage('Test category 0 [EN] šÄßüл');
        $I->dontSeeElement('#scRootCatChanged');

        $homePage->openCategoryPage('Kiteboarding');
        $I->waitForElementVisible('#scRootCatChanged', 5);

        $I->click(Translator::translate('CONTINUE_SHOPPING'));
        $I->dontSee('scRootCatChanged');
        $homePage->checkBasketEmpty();
    }

    public function checkIfblBasketExcludeEnabledAlsoClearsByEmptyBasket(AcceptanceTester $I)
    {
        $I->wantToTest('Test if blBasketExcludeEnabled rootCatChange is no longer blocked by an empty basket.');

        $I->updateConfigInDatabase('blBasketExcludeEnabled', 'true', 'bool');
        $I->clearShopCache();

        $homePage = $I->openShop();

        $homePage->openCategoryPage('Test category 0 [EN] šÄßüл')
            ->openDetailsPage(1)
            ->addProductToBasket(1)
            ->openBasket();

        $homePage->openCategoryPage('Kiteboarding');
        $I->waitForElementVisible('#scRootCatChanged', 5);

        $basket = $homePage->openBasket();
        $basket->updateProductAmount(0);
        
        $homePage->openCategoryPage('Kiteboarding');
        $I->dontSeeElement('#scRootCatChanged');
    }

    public function testPrivateShoppingBasketExpiration(AcceptanceTester $I)
    {
        $I->wantToTest('Test private basket reservation expiration');

        $I->updateInDatabase('oxarticles', ['oxstock' => '2', 'oxstockflag' => '2'], ['oxid' => '1000']);
        $I->updateConfigInDatabase('blPsBasketReservationEnabled', 'true', 'bool');
        $I->updateConfigInDatabase('iPsBasketReservationTimeout', '10', 'str');

        $I->clearShopCache();
        $homePage = $I->openShop();

        $homePage->openCategoryPage('Test category 0 [EN] šÄßüл')
            ->openDetailsPage(1)
            ->addProductToBasket(2);

        $homePage->seeCountdownWithinBasket();

        $I->openShop()->searchFor('1000');
        $I->see(Translator::translate('NO_ITEMS_FOUND'));
        //we need to wait for the timeout
        $I->wait(10);

        $homePage = $I->openShop();
        $homePage->checkBasketEmpty();
        $homePage->searchFor('1000');
        $I->dontSee(Translator::translate('NO_ITEMS_FOUND'));
    }
}

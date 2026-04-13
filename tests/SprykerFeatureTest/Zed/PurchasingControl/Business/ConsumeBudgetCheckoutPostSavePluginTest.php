<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Checkout\ConsumeBudgetCheckoutPostSavePlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group CostCenter
 * @group Business
 * @group ConsumeBudgetCheckoutPostSavePluginTest
 */
class ConsumeBudgetCheckoutPostSavePluginTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    public function testExecuteHookConsumesBudgetWhenBudgetIsSelected(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setTotals((new TotalsTransfer())->setGrandTotal(7500));

        $checkoutResponseTransfer = (new CheckoutResponseTransfer())
            ->setSaveOrder((new SaveOrderTransfer())->setIdSalesOrder($idSalesOrder));

        (new ConsumeBudgetCheckoutPostSavePlugin())->executeHook($quoteTransfer, $checkoutResponseTransfer);

        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkBudget($budgetTransfer->getIdBudgetOrFail())
            ->filterByFkSalesOrder($idSalesOrder)
            ->count();

        $this->assertSame(1, $consumptionCount);
    }

    public function testExecuteHookIsNoOpWhenNoBudgetOnQuote(): void
    {
        $consumptionCountBefore = SpyBudgetConsumptionQuery::create()->count();

        $quoteTransfer = new QuoteTransfer();
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        (new ConsumeBudgetCheckoutPostSavePlugin())->executeHook($quoteTransfer, $checkoutResponseTransfer);

        $this->assertSame($consumptionCountBefore, SpyBudgetConsumptionQuery::create()->count());
    }
}

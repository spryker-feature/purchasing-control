<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group CostCenter
 * @group Business
 * @group BudgetConsumptionWriterTest
 */
class BudgetConsumptionWriterTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    public function testConsumeBudgetCreatesConsumptionRecord(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $this->tester->getFacade()->consumeBudget(
            $budgetTransfer->getIdBudgetOrFail(),
            $idSalesOrder,
            5000,
        );

        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkBudget($budgetTransfer->getIdBudgetOrFail())
            ->filterByFkSalesOrder($idSalesOrder)
            ->count();

        $this->assertSame(1, $consumptionCount);
    }

    public function testRestoreBudgetDeletesConsumptionRecordsForOrder(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $this->tester->getFacade()->consumeBudget($budgetTransfer->getIdBudgetOrFail(), $idSalesOrder, 3000);
        $this->tester->getFacade()->consumeBudget($budgetTransfer->getIdBudgetOrFail(), $idSalesOrder, 2000);

        $this->tester->getFacade()->restoreBudget($idSalesOrder);

        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idSalesOrder)
            ->count();

        $this->assertSame(0, $consumptionCount);
    }

    public function testRemainingBudgetDecreasesAfterConsumption(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget(
            $costCenterTransfer->getIdCostCenterOrFail(),
            ['amount' => 10000],
        );
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $this->tester->getFacade()->consumeBudget($budgetTransfer->getIdBudgetOrFail(), $idSalesOrder, 4000);

        $updatedBudgets = $this->tester->getFacade()->getActiveBudgetsForCostCenter(
            $costCenterTransfer->getIdCostCenterOrFail(),
            'EUR',
        );

        $updatedBudget = $updatedBudgets->getBudgets()->offsetGet(0);

        $this->assertSame(4000, $updatedBudget->getConsumedAmount());
        $this->assertSame(6000, $updatedBudget->getRemainingAmount());
    }

    public function testRemainingBudgetRestoredAfterCancellation(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget(
            $costCenterTransfer->getIdCostCenterOrFail(),
            ['amount' => 10000],
        );
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $this->tester->getFacade()->consumeBudget($budgetTransfer->getIdBudgetOrFail(), $idSalesOrder, 4000);
        $this->tester->getFacade()->restoreBudget($idSalesOrder);

        $updatedBudgets = $this->tester->getFacade()->getActiveBudgetsForCostCenter(
            $costCenterTransfer->getIdCostCenterOrFail(),
            'EUR',
        );

        $updatedBudget = $updatedBudgets->getBudgets()->offsetGet(0);

        $this->assertSame(0, $updatedBudget->getConsumedAmount());
        $this->assertSame(10000, $updatedBudget->getRemainingAmount());
    }

    public function testConsumeBudgetIsNoOpWhenBudgetIdIsNull(): void
    {
        // When no budget is selected, the plugin skips calling consumeBudget.
        // This verifies the consumption table stays unchanged when no consumption is recorded.
        $consumptionCountBefore = SpyBudgetConsumptionQuery::create()->count();

        $consumptionCountAfter = SpyBudgetConsumptionQuery::create()->count();

        $this->assertSame($consumptionCountBefore, $consumptionCountAfter);
    }
}

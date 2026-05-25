<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Checkout\CostCenterOrderSaverPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrderSaverPluginTest
 */
class CostCenterOrderSaverPluginTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testSaveOrderSavesCostCenterAndBudgetReferencesToOrder(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $quoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail());

        $saveOrderTransfer = (new SaveOrderTransfer())->setIdSalesOrder($idSalesOrder);

        // Act
        (new CostCenterOrderSaverPlugin())->saveOrder($quoteTransfer, $saveOrderTransfer);

        // Assert
        $orderEntity = SpySalesOrderQuery::create()->findPk($idSalesOrder);

        $this->assertSame($costCenterTransfer->getIdCostCenterOrFail(), $orderEntity->getFkCostCenter());
        $this->assertSame($budgetTransfer->getIdBudgetOrFail(), $orderEntity->getFkBudget());
    }

    public function testSaveOrderIsNoOpWhenNoCostCenterOnQuote(): void
    {
        // Arrange
        $idSalesOrder = $this->tester->haveSalesOrderId();

        $quoteTransfer = new QuoteTransfer();
        $saveOrderTransfer = (new SaveOrderTransfer())->setIdSalesOrder($idSalesOrder);

        // Act
        (new CostCenterOrderSaverPlugin())->saveOrder($quoteTransfer, $saveOrderTransfer);

        // Assert
        $orderEntity = SpySalesOrderQuery::create()->findPk($idSalesOrder);

        $this->assertNull($orderEntity->getFkCostCenter());
        $this->assertNull($orderEntity->getFkBudget());
    }
}

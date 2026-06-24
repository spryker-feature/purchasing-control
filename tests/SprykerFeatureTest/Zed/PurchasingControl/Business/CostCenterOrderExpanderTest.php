<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderExpander;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrderExpanderTest
 */
class CostCenterOrderExpanderTest extends Unit
{
    public function testExpandOrderReturnsUnchangedWhenFkCostCenterIsNull(): void
    {
        // Arrange
        $costCenterReader = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReader->expects($this->never())->method('getCostCenterCollection');

        $orderTransfer = new OrderTransfer();

        // Act
        $result = $this->createExpander($costCenterReader)->expandOrder($orderTransfer);

        // Assert
        $this->assertNull($result->getCostCenter());
        $this->assertNull($result->getBudget());
    }

    public function testExpandOrderReturnsUnchangedWhenCostCenterCollectionIsEmpty(): void
    {
        // Arrange
        $costCenterReader = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReader->method('getCostCenterCollection')
            ->willReturn(new CostCenterCollectionTransfer());

        $orderTransfer = (new OrderTransfer())->setFkCostCenter(1);

        // Act
        $result = $this->createExpander($costCenterReader)->expandOrder($orderTransfer);

        // Assert
        $this->assertNull($result->getCostCenter());
        $this->assertNull($result->getBudget());
    }

    public function testExpandOrderSetsCostCenterAndSkipsBudgetWhenFkBudgetIsNull(): void
    {
        // Arrange
        $costCenterTransfer = (new CostCenterTransfer())->setIdCostCenter(1)->setName('Marketing');

        $costCenterReader = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReader->method('getCostCenterCollection')
            ->willReturn((new CostCenterCollectionTransfer())->addCostCenter($costCenterTransfer));

        $budgetReader = $this->createMock(BudgetReaderInterface::class);
        $budgetReader->expects($this->never())->method('getBudgetCollection');

        $orderTransfer = (new OrderTransfer())->setFkCostCenter(1);

        // Act
        $result = $this->createExpander($costCenterReader, $budgetReader)->expandOrder($orderTransfer);

        // Assert
        $this->assertSame('Marketing', $result->getCostCenterOrFail()->getName());
        $this->assertNull($result->getBudget());
    }

    public function testExpandOrderSetsCostCenterAndSkipsBudgetWhenBudgetCollectionIsEmpty(): void
    {
        // Arrange
        $costCenterTransfer = (new CostCenterTransfer())->setIdCostCenter(1)->setName('Marketing');

        $costCenterReader = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReader->method('getCostCenterCollection')
            ->willReturn((new CostCenterCollectionTransfer())->addCostCenter($costCenterTransfer));

        $budgetReader = $this->createMock(BudgetReaderInterface::class);
        $budgetReader->method('getBudgetCollection')
            ->willReturn(new BudgetCollectionTransfer());

        $orderTransfer = (new OrderTransfer())->setFkCostCenter(1)->setFkBudget(99);

        // Act
        $result = $this->createExpander($costCenterReader, $budgetReader)->expandOrder($orderTransfer);

        // Assert
        $this->assertSame('Marketing', $result->getCostCenterOrFail()->getName());
        $this->assertNull($result->getBudget());
    }

    public function testExpandOrderSetsCostCenterAndBudgetWhenBothAreFound(): void
    {
        // Arrange
        $costCenterTransfer = (new CostCenterTransfer())->setIdCostCenter(1)->setName('Marketing');
        $budgetTransfer = (new BudgetTransfer())->setIdBudget(10)->setName('Q1 Budget');

        $costCenterReader = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReader->method('getCostCenterCollection')
            ->willReturn((new CostCenterCollectionTransfer())->addCostCenter($costCenterTransfer));

        $budgetReader = $this->createMock(BudgetReaderInterface::class);
        $budgetReader->method('getBudgetCollection')
            ->willReturn((new BudgetCollectionTransfer())->addBudget($budgetTransfer));

        $orderTransfer = (new OrderTransfer())->setFkCostCenter(1)->setFkBudget(10);

        // Act
        $result = $this->createExpander($costCenterReader, $budgetReader)->expandOrder($orderTransfer);

        // Assert
        $this->assertSame('Marketing', $result->getCostCenterOrFail()->getName());
        $this->assertSame('Q1 Budget', $result->getBudgetOrFail()->getName());
    }

    protected function createExpander(
        ?CostCenterReaderInterface $costCenterReader = null,
        ?BudgetReaderInterface $budgetReader = null,
    ): CostCenterOrderExpander {
        return new CostCenterOrderExpander(
            $costCenterReader ?? $this->createMock(CostCenterReaderInterface::class),
            $budgetReader ?? $this->createMock(BudgetReaderInterface::class),
            $this->createMock(CompanyFacadeInterface::class),
        );
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use Spryker\Shared\Kernel\BundleConfigMock\BundleConfigMock;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use Spryker\Zed\Sales\SalesDependencyProvider;
use Spryker\Zed\Shipment\Communication\Plugin\ShipmentOrderHydratePlugin;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Oms\RestoreBudgetOnRefundOmsCommandPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group RestoreBudgetOnRefundOmsCommandPluginTest
 */
class RestoreBudgetOnRefundOmsCommandPluginTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
        $this->tester->setDependency(
            SalesDependencyProvider::HYDRATE_ORDER_PLUGINS,
            [new ShipmentOrderHydratePlugin()],
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new BundleConfigMock())->reset();
    }

    public function testRunAlwaysReturnsEmptyArray(): void
    {
        // Arrange
        $orderEntity = $this->tester->haveSalesOrderEntity();

        // Act
        $result = (new RestoreBudgetOnRefundOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $this->assertSame([], $result);
    }

    public function testRunIsNoOpForOrderWithoutBudget(): void
    {
        // Arrange
        $orderEntity = $this->tester->haveSalesOrderEntity();
        $consumptionCountBefore = SpyBudgetConsumptionQuery::create()->count();

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $this->assertSame($consumptionCountBefore, SpyBudgetConsumptionQuery::create()->count());
    }

    public function testRunIsNoOpWhenOrderHasBudgetButNoConsumptionRecord(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);
        $consumptionCountBefore = SpyBudgetConsumptionQuery::create()->count();

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $this->assertSame($consumptionCountBefore, SpyBudgetConsumptionQuery::create()->count());
    }

    public function testRunReducesConsumptionByItemRefundableAmountWhenOneItemRefunded(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $refundedItem = $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 3000]);
        $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 2000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5000);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItem], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(2000, (int)$consumption->getAmount());
    }

    public function testRunReducesConsumptionBySumOfRefundedItemAmountsWhenMultipleItemsRefunded(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $refundedItemA = $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 2000]);
        $refundedItemB = $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 1500]);
        $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 1000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 4500);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItemA, $refundedItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(1000, (int)$consumption->getAmount());
    }

    public function testRunDeletesConsumptionWhenRemainingAmountDropsToZero(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $refundedItemA = $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 3000]);
        $refundedItemB = $this->tester->haveSalesOrderItem($idOrder, ['refundableAmount' => 2000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5000);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItemA, $refundedItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->count();

        $this->assertSame(0, $consumptionCount);
    }

    public function testRunDoesNotDeductShipmentCostByDefault(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment = $this->tester->haveSalesShipment($idOrder, $expense->getIdSalesExpense());
        $refundedItemA = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 2000,
            'fkSalesShipment' => $shipment->getIdSalesShipment(),
        ]);
        $refundedItemB = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1500,
            'fkSalesShipment' => $shipment->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 4000);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItemA, $refundedItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert — shipment not included because isRefundWithShipmentEnabled defaults to false
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(500, (int)$consumption->getAmount());
    }

    public function testRunDeductsShipmentCostWhenAllItemsInGroupAreRefundedAndShipmentEnabled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense1 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment1 = $this->tester->haveSalesShipment($idOrder, $expense1->getIdSalesExpense());
        $refundedItemA = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 2000,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);
        $refundedItemB = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1500,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);

        $expense2 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 300, 'refundableAmount' => 300]);
        $shipment2 = $this->tester->haveSalesShipment($idOrder, $expense2->getIdSalesExpense());
        $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1000,
            'fkSalesShipment' => $shipment2->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5300);

        $this->tester->mockPurchasingControlConfig('isRefundWithShipmentEnabled', true);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItemA, $refundedItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(1300, (int)$consumption->getAmount());
    }

    public function testRunDoesNotDeductShipmentCostWhenOnlyOneItemInGroupIsRefunded(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense1 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment1 = $this->tester->haveSalesShipment($idOrder, $expense1->getIdSalesExpense());
        $refundedItemA = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 2000,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);
        $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1500,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);

        $expense2 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 300, 'refundableAmount' => 300]);
        $shipment2 = $this->tester->haveSalesShipment($idOrder, $expense2->getIdSalesExpense());
        $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1000,
            'fkSalesShipment' => $shipment2->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5300);

        $this->tester->mockPurchasingControlConfig('isRefundWithShipmentEnabled', true);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$refundedItemA], $orderEntity, new ReadOnlyArrayObject());

        // Assert — only item amount deducted; group 1 not fully refunded so no shipment deduction
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(3300, (int)$consumption->getAmount());
    }

    public function testRunDeductsShipmentCostWhenLastItemIsRefundedAndFirstIsAlreadyRefunded(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment = $this->tester->haveSalesShipment($idOrder, $expense->getIdSalesExpense());
        $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 2000,
            'stateName' => 'refunded',
            'fkSalesShipment' => $shipment->getIdSalesShipment(),
        ]);
        $lastRefundedItem = $this->tester->haveSalesOrderItem($idOrder, [
            'refundableAmount' => 1500,
            'fkSalesShipment' => $shipment->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 4000);

        $this->tester->mockPurchasingControlConfig('isRefundWithShipmentEnabled', true);

        // Act
        (new RestoreBudgetOnRefundOmsCommandPlugin())->run([$lastRefundedItem], $orderEntity, new ReadOnlyArrayObject());

        // Assert — item (1500) + shipment (500) deducted because first item is already in 'refunded' state
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(2000, (int)$consumption->getAmount());
    }
}

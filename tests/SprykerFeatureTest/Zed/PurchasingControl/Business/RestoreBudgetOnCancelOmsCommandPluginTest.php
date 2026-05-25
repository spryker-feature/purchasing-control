<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use Spryker\Zed\Sales\SalesDependencyProvider;
use Spryker\Zed\Shipment\Communication\Plugin\ShipmentOrderHydratePlugin;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Oms\RestoreBudgetOnCancelOmsCommandPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group RestoreBudgetOnCancelOmsCommandPluginTest
 */
class RestoreBudgetOnCancelOmsCommandPluginTest extends Unit
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

    public function testRunAlwaysReturnsEmptyArray(): void
    {
        // Arrange
        $orderEntity = $this->tester->haveSalesOrderEntity();

        // Act
        $result = (new RestoreBudgetOnCancelOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $this->assertSame([], $result);
    }

    public function testRunIsNoOpForOrderWithoutBudget(): void
    {
        // Arrange
        $orderEntity = $this->tester->haveSalesOrderEntity();
        $consumptionCountBefore = SpyBudgetConsumptionQuery::create()->count();

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

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
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $this->assertSame($consumptionCountBefore, SpyBudgetConsumptionQuery::create()->count());
    }

    public function testRunDeletesConsumptionRecordWhenAllItemsAreCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $this->tester->haveBudgetConsumption(
            $budgetTransfer->getIdBudgetOrFail(),
            $orderEntity->getIdSalesOrder(),
            5000,
        );

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($orderEntity->getIdSalesOrder())
            ->count();

        $this->assertSame(0, $consumptionCount);
    }

    public function testRunDeletesConsumptionWhenAllOrderItemsAreExplicitlyPassedAsCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $itemA = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 3000]);
        $itemB = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 2000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5000);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$itemA, $itemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->count();

        $this->assertSame(0, $consumptionCount);
    }

    public function testRunDeletesConsumptionWhenLastItemIsCanceledAndFirstIsAlreadyCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 3000, 'stateName' => 'cancelled']);
        $canceledItem = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 2000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5000);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$canceledItem], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumptionCount = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->count();

        $this->assertSame(0, $consumptionCount);
    }

    public function testRunReducesConsumptionByItemAmountWhenOneItemCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $canceledItem = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 3000]);
        $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 2000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5000);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$canceledItem], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(2000, (int)$consumption->getAmount());
    }

    public function testRunReducesConsumptionBySumOfCanceledItemAmountsWhenMultipleItemsCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $canceledItemA = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 2000]);
        $canceledItemB = $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 1500]);
        $this->tester->haveSalesOrderItem($idOrder, ['priceToPayAggregation' => 1000]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 4500);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$canceledItemA, $canceledItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(1000, (int)$consumption->getAmount());
    }

    public function testRunDeductsShipmentCostWhenAllItemsInGroupAreCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense1 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment1 = $this->tester->haveSalesShipment($idOrder, $expense1->getIdSalesExpense());
        $canceledItemA = $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 2000,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);
        $canceledItemB = $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 1500,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);

        $expense2 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 300, 'refundableAmount' => 300]);
        $shipment2 = $this->tester->haveSalesShipment($idOrder, $expense2->getIdSalesExpense());
        $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 1000,
            'fkSalesShipment' => $shipment2->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5300);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$canceledItemA, $canceledItemB], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(1300, (int)$consumption->getAmount());
    }

    public function testRunDoesNotDeductShipmentCostWhenOnlyOneItemInGroupIsCanceled(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $orderEntity = $this->tester->haveSalesOrderEntity(['fkBudget' => $budgetTransfer->getIdBudgetOrFail()]);

        $idOrder = $orderEntity->getIdSalesOrder();

        $expense1 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 500, 'refundableAmount' => 500]);
        $shipment1 = $this->tester->haveSalesShipment($idOrder, $expense1->getIdSalesExpense());
        $canceledItemA = $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 2000,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);
        $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 1500,
            'fkSalesShipment' => $shipment1->getIdSalesShipment(),
        ]);

        $expense2 = $this->tester->haveSalesExpense($idOrder, ['grossPrice' => 300, 'refundableAmount' => 300]);
        $shipment2 = $this->tester->haveSalesShipment($idOrder, $expense2->getIdSalesExpense());
        $this->tester->haveSalesOrderItem($idOrder, [
            'priceToPayAggregation' => 1000,
            'fkSalesShipment' => $shipment2->getIdSalesShipment(),
        ]);

        $this->tester->haveBudgetConsumption($budgetTransfer->getIdBudgetOrFail(), $idOrder, 5300);

        // Act
        (new RestoreBudgetOnCancelOmsCommandPlugin())->run([$canceledItemA], $orderEntity, new ReadOnlyArrayObject());

        // Assert
        $consumption = SpyBudgetConsumptionQuery::create()
            ->filterByFkSalesOrder($idOrder)
            ->findOne();

        $this->assertSame(3300, (int)$consumption->getAmount());
    }
}

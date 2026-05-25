<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;

class BudgetRefundRestorer implements BudgetRefundRestorerInterface
{
    public function __construct(
        protected readonly SalesFacadeInterface $salesFacade,
        protected readonly BudgetConsumptionReaderInterface $budgetConsumptionReader,
        protected readonly BudgetConsumptionApplierInterface $budgetConsumptionApplier,
        protected readonly OrderItemExtractorInterface $orderItemExtractor,
        protected readonly ShipmentExpenseCalculatorInterface $shipmentExpenseCalculator,
        protected readonly PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    public function restoreBudgetForRefundedItems(array $orderItemEntities, SpySalesOrder $orderEntity): void
    {
        $idSalesOrder = $orderEntity->getIdSalesOrder();
        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        if (!$orderTransfer || !$orderTransfer->getFkBudget()) {
            return;
        }

        $budgetConsumptionTransfer = $this->budgetConsumptionReader->findBudgetConsumptionByIdSalesOrder($idSalesOrder);
        if (!$budgetConsumptionTransfer) {
            return;
        }

        $refundedOrderItemIds = $this->orderItemExtractor->extractOrderItemIds($orderItemEntities);
        $refundedItemTransfers = $this->orderItemExtractor->filterItemTransfersByIds($orderTransfer, $refundedOrderItemIds);
        $deductionAmount = $this->calculateRefundDeductionAmount($refundedItemTransfers, $orderTransfer);
        $this->budgetConsumptionApplier->applyBudgetDeduction($budgetConsumptionTransfer, $deductionAmount, $idSalesOrder);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $refundedItemTransfers
     */
    protected function calculateRefundDeductionAmount(array $refundedItemTransfers, OrderTransfer $orderTransfer): int
    {
        $itemsAmount = array_sum(array_map(
            static fn (ItemTransfer $itemTransfer) => $itemTransfer->getRefundableAmount() ?: $itemTransfer->getSumPriceToPayAggregation() ?? 0,
            $refundedItemTransfers,
        ));

        if (!$this->purchasingControlConfig->isRefundWithShipmentEnabled()) {
            return $itemsAmount;
        }

        $refundedShipmentIds = $this->shipmentExpenseCalculator->calculateFullyRefundedShipmentGroupIds(
            $refundedItemTransfers,
            $orderTransfer,
        );

        return $itemsAmount + $this->shipmentExpenseCalculator->calculateShipmentExpensesAmount($refundedShipmentIds, $orderTransfer);
    }
}

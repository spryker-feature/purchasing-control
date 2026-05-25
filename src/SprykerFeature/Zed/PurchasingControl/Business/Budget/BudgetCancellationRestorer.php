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

class BudgetCancellationRestorer implements BudgetCancellationRestorerInterface
{
    public function __construct(
        protected readonly SalesFacadeInterface $salesFacade,
        protected readonly BudgetConsumptionReaderInterface $budgetConsumptionReader,
        protected readonly BudgetConsumptionApplierInterface $budgetConsumptionApplier,
        protected readonly OrderItemExtractorInterface $orderItemExtractor,
        protected readonly OrderItemStateCheckerInterface $orderItemStateChecker,
        protected readonly ShipmentExpenseCalculatorInterface $shipmentExpenseCalculator,
    ) {
    }

    public function restoreBudgetForCanceledItems(array $orderItemEntities, SpySalesOrder $orderEntity): void
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

        $canceledOrderItemIds = $this->orderItemExtractor->extractOrderItemIds($orderItemEntities);

        if ($this->areAllItemsCanceled($canceledOrderItemIds, $orderTransfer)) {
            $this->budgetConsumptionApplier->deleteBudgetConsumption($idSalesOrder);

            return;
        }

        $canceledItemTransfers = $this->orderItemExtractor->filterItemTransfersByIds($orderTransfer, $canceledOrderItemIds);
        $deductionAmount = $this->calculateCancellationDeductionAmount($canceledItemTransfers, $orderTransfer);
        $this->budgetConsumptionApplier->applyBudgetDeduction($budgetConsumptionTransfer, $deductionAmount, $idSalesOrder);
    }

    /**
     * @param array<int> $canceledOrderItemIds
     */
    protected function areAllItemsCanceled(array $canceledOrderItemIds, OrderTransfer $orderTransfer): bool
    {
        $canceledIdIndex = array_flip($canceledOrderItemIds);

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (!isset($canceledIdIndex[$itemTransfer->getIdSalesOrderItemOrFail()]) && !$this->orderItemStateChecker->isItemAlreadyCanceled($itemTransfer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $canceledItemTransfers
     */
    protected function calculateCancellationDeductionAmount(array $canceledItemTransfers, OrderTransfer $orderTransfer): int
    {
        $itemsAmount = array_sum(array_map(
            static fn (ItemTransfer $itemTransfer) => $itemTransfer->getSumPriceToPayAggregation() ?? 0,
            $canceledItemTransfers,
        ));

        $canceledShipmentIds = $this->shipmentExpenseCalculator->calculateFullyCanceledShipmentGroupIds(
            $canceledItemTransfers,
            $orderTransfer,
        );

        return $itemsAmount + $this->shipmentExpenseCalculator->calculateShipmentExpensesAmount($canceledShipmentIds, $orderTransfer);
    }
}

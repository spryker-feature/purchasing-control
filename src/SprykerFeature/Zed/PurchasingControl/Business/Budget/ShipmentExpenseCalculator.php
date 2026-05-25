<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;

class ShipmentExpenseCalculator implements ShipmentExpenseCalculatorInterface
{
    /**
     * @uses \Spryker\Shared\Shipment\ShipmentConfig::SHIPMENT_EXPENSE_TYPE.
     */
    protected const string SHIPMENT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    public function __construct(
        protected readonly OrderItemStateCheckerInterface $orderItemStateChecker,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function calculateFullyCanceledShipmentGroupIds(array $canceledItemTransfers, OrderTransfer $orderTransfer): array
    {
        return $this->calculateFullyProcessedShipmentGroupIds(
            $canceledItemTransfers,
            $orderTransfer,
            fn (ItemTransfer $itemTransfer) => $this->orderItemStateChecker->isItemAlreadyCanceled($itemTransfer),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function calculateFullyRefundedShipmentGroupIds(array $refundedItemTransfers, OrderTransfer $orderTransfer): array
    {
        return $this->calculateFullyProcessedShipmentGroupIds(
            $refundedItemTransfers,
            $orderTransfer,
            fn (ItemTransfer $itemTransfer) => $this->orderItemStateChecker->isItemAlreadyRefunded($itemTransfer),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function calculateShipmentExpensesAmount(array $shipmentIds, OrderTransfer $orderTransfer): int
    {
        if ($shipmentIds === []) {
            return 0;
        }

        $shipmentIdIndex = array_flip($shipmentIds);
        $amount = 0;

        foreach ($orderTransfer->getExpenses() as $expense) {
            if ($expense->getType() !== static::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            $idShipment = $expense->getShipment()?->getIdSalesShipment();
            if ($idShipment !== null && isset($shipmentIdIndex[$idShipment])) {
                $amount += $expense->getRefundableAmount() ?: $expense->getSumPriceToPayAggregation() ?? 0;
            }
        }

        return $amount;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $processedItemTransfers
     *
     * @return array<int>
     */
    protected function calculateFullyProcessedShipmentGroupIds(
        array $processedItemTransfers,
        OrderTransfer $orderTransfer,
        callable $isAlreadyProcessed,
    ): array {
        $currentProcessedItemIdIndex = [];
        foreach ($processedItemTransfers as $itemTransfer) {
            $currentProcessedItemIdIndex[$itemTransfer->getIdSalesOrderItemOrFail()] = true;
        }

        $allProcessedItemIdIndex = $currentProcessedItemIdIndex;
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (!isset($allProcessedItemIdIndex[$itemTransfer->getIdSalesOrderItemOrFail()]) && $isAlreadyProcessed($itemTransfer)) {
                $allProcessedItemIdIndex[$itemTransfer->getIdSalesOrderItemOrFail()] = true;
            }
        }

        $shipmentGroupItems = [];
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $idShipment = $itemTransfer->getShipment()?->getIdSalesShipment();
            if ($idShipment === null) {
                continue;
            }

            $shipmentGroupItems[$idShipment][] = $itemTransfer->getIdSalesOrderItemOrFail();
        }

        $fullyProcessedShipmentIds = [];
        foreach ($shipmentGroupItems as $idShipment => $itemIds) {
            if (count(array_intersect($itemIds, array_keys($currentProcessedItemIdIndex))) === 0) {
                continue;
            }

            if (count(array_diff($itemIds, array_keys($allProcessedItemIdIndex))) === 0) {
                $fullyProcessedShipmentIds[] = $idShipment;
            }
        }

        return $fullyProcessedShipmentIds;
    }
}

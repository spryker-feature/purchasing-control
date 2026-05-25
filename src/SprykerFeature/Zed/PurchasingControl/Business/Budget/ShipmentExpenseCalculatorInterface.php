<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\OrderTransfer;

interface ShipmentExpenseCalculatorInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $canceledItemTransfers
     *
     * @return array<int>
     */
    public function calculateFullyCanceledShipmentGroupIds(array $canceledItemTransfers, OrderTransfer $orderTransfer): array;

    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $refundedItemTransfers
     *
     * @return array<int>
     */
    public function calculateFullyRefundedShipmentGroupIds(array $refundedItemTransfers, OrderTransfer $orderTransfer): array;

    /**
     * @param array<int> $shipmentIds
     */
    public function calculateShipmentExpensesAmount(array $shipmentIds, OrderTransfer $orderTransfer): int;
}

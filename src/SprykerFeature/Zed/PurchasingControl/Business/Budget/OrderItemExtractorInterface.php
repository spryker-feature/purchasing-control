<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\OrderTransfer;

interface OrderItemExtractorInterface
{
    /**
     * @param array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $orderItemEntities
     *
     * @return array<int>
     */
    public function extractOrderItemIds(array $orderItemEntities): array;

    /**
     * @param array<int> $itemIds
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    public function filterItemTransfersByIds(OrderTransfer $orderTransfer, array $itemIds): array;
}

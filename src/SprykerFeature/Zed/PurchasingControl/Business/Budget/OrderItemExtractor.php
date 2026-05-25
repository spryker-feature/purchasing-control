<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;

class OrderItemExtractor implements OrderItemExtractorInterface
{
    /**
     * {@inheritDoc}
     */
    public function extractOrderItemIds(array $orderItemEntities): array
    {
        return array_map(
            static fn ($orderItemEntity) => $orderItemEntity->getIdSalesOrderItem(),
            $orderItemEntities,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function filterItemTransfersByIds(OrderTransfer $orderTransfer, array $itemIds): array
    {
        return array_filter(
            $orderTransfer->getItems()->getArrayCopy(),
            static fn (ItemTransfer $itemTransfer) => in_array($itemTransfer->getIdSalesOrderItemOrFail(), $itemIds, true),
        );
    }
}

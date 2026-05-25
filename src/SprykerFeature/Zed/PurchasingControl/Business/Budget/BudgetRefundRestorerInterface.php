<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Orm\Zed\Sales\Persistence\SpySalesOrder;

interface BudgetRefundRestorerInterface
{
    /**
     * @param array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $orderItemEntities
     */
    public function restoreBudgetForRefundedItems(array $orderItemEntities, SpySalesOrder $orderEntity): void;
}

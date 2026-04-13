<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Order;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;

interface CostCenterOrderSaverInterface
{
    /**
     * Specification:
     * - Copies fk_cost_center and fk_budget from the quote to the sales order.
     * - Does nothing when no cost center is selected on the quote.
     */
    public function saveCostCenterToOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;
}

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
    public function saveCostCenterToOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;
}

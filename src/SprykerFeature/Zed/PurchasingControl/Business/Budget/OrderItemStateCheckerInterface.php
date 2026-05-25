<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\ItemTransfer;

interface OrderItemStateCheckerInterface
{
    public function isItemAlreadyCanceled(ItemTransfer $itemTransfer): bool;

    public function isItemAlreadyRefunded(ItemTransfer $itemTransfer): bool;
}

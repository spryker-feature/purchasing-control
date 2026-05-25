<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\ItemTransfer;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;

class OrderItemStateChecker implements OrderItemStateCheckerInterface
{
    public function __construct(
        protected readonly PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    public function isItemAlreadyCanceled(ItemTransfer $itemTransfer): bool
    {
        return $this->isItemInState($itemTransfer, $this->purchasingControlConfig->getOmsItemStateNamesForCancellationBudgetRelease());
    }

    public function isItemAlreadyRefunded(ItemTransfer $itemTransfer): bool
    {
        return $this->isItemInState($itemTransfer, $this->purchasingControlConfig->getOmsItemStateNamesForRefundBudgetRelease());
    }

    /**
     * @param array<string> $stateNames
     */
    protected function isItemInState(ItemTransfer $itemTransfer, array $stateNames): bool
    {
        $stateName = $itemTransfer->getState()?->getName();

        return $stateName !== null && in_array($stateName, $stateNames, true);
    }
}

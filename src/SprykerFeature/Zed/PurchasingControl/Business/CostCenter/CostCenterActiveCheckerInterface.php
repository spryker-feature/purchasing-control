<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface CostCenterActiveCheckerInterface
{
    public function isCostCenterActiveForQuote(BudgetTransfer $budgetTransfer, QuoteTransfer $quoteTransfer): bool;

    public function hasActiveCostCentersForQuote(QuoteTransfer $quoteTransfer): bool;
}

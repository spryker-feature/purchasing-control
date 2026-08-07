<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteBudgetReaderInterface
{
    public function findBudgetForQuote(QuoteTransfer $quoteTransfer, bool $withBudgetConsumption = false): ?BudgetTransfer;
}

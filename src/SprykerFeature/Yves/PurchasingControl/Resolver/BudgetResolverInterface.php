<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Resolver;

use ArrayObject;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface BudgetResolverInterface
{
    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     */
    public function resolveSelectedBudget(ArrayObject $budgetTransfers, QuoteTransfer $quoteTransfer): ?BudgetTransfer;
}

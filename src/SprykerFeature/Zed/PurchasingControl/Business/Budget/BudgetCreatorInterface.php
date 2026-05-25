<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;

interface BudgetCreatorInterface
{
    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;
}

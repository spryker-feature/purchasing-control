<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetResponseTransfer;
use Generated\Shared\Transfer\BudgetTransfer;

interface BudgetWriterInterface
{
    public function createBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer;

    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\BudgetTransfer;

interface BudgetReaderInterface
{
    /**
     * Finds a budget by UUID, verifying it belongs to a cost center owned by the given company.
     * Returns null if the cost center does not belong to the company or the budget does not exist.
     */
    public function findBudget(string $budgetUuid, string $costCenterUuid, int $idCompany): ?BudgetTransfer;
}

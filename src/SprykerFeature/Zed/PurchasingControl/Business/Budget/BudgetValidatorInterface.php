<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Generated\Shared\Transfer\BudgetTransfer;

interface BudgetValidatorInterface
{
    /**
     * @return array<\Generated\Shared\Transfer\ErrorTransfer>
     */
    public function validateBudget(BudgetTransfer $budgetTransfer, ?int $idCompany = null): array;

    /**
     * @return array<int, true>
     */
    public function validateBudgetCollection(
        BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer,
        BudgetCollectionResponseTransfer $responseTransfer,
    ): array;
}

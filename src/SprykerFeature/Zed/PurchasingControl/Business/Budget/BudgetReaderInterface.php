<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetTransfer;

interface BudgetReaderInterface
{
    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer;

    public function getBudgetById(int $idBudget): BudgetTransfer;
}

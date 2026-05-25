<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;

interface BudgetConsumptionApplierInterface
{
    public function applyBudgetDeduction(BudgetConsumptionTransfer $budgetConsumptionTransfer, int $deductionAmount, int $idSalesOrder): void;

    public function deleteBudgetConsumption(int $idSalesOrder): void;
}

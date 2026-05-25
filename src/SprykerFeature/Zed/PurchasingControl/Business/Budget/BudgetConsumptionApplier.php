<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class BudgetConsumptionApplier implements BudgetConsumptionApplierInterface
{
    public function __construct(
        protected readonly PurchasingControlEntityManagerInterface $purchasingControlEntityManager,
    ) {
    }

    public function applyBudgetDeduction(BudgetConsumptionTransfer $budgetConsumptionTransfer, int $deductionAmount, int $idSalesOrder): void
    {
        $remainingAmount = $budgetConsumptionTransfer->getAmountOrFail() - $deductionAmount;

        if ($remainingAmount <= 0) {
            $this->purchasingControlEntityManager->deleteBudgetConsumptionByIdSalesOrder($idSalesOrder);

            return;
        }

        $budgetConsumptionTransfer->setAmount($remainingAmount);
        $this->purchasingControlEntityManager->updateBudgetConsumption($budgetConsumptionTransfer);
    }

    public function deleteBudgetConsumption(int $idSalesOrder): void
    {
        $this->purchasingControlEntityManager->deleteBudgetConsumptionByIdSalesOrder($idSalesOrder);
    }
}

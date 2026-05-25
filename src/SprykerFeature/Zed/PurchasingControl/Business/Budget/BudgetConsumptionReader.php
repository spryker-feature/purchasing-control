<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConsumptionConditionsTransfer;
use Generated\Shared\Transfer\BudgetConsumptionCriteriaTransfer;
use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class BudgetConsumptionReader implements BudgetConsumptionReaderInterface
{
    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $purchasingControlRepository,
    ) {
    }

    public function findBudgetConsumptionByIdSalesOrder(int $idSalesOrder): ?BudgetConsumptionTransfer
    {
        $budgetConsumptionCollection = $this->purchasingControlRepository->getBudgetConsumptionCollection(
            (new BudgetConsumptionCriteriaTransfer())->setBudgetConsumptionConditions(
                (new BudgetConsumptionConditionsTransfer())->addIdSalesOrder($idSalesOrder),
            ),
        );

        return $budgetConsumptionCollection->getBudgetConsumptions()->getIterator()->current() ?: null;
    }
}

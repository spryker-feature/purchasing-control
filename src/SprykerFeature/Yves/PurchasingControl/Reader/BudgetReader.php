<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface;

class BudgetReader implements BudgetReaderInterface
{
    public function __construct(
        protected readonly PurchasingControlClientInterface $purchasingControlClient,
        protected readonly CostCenterReaderInterface $costCenterReader,
    ) {
    }

    public function findBudget(string $budgetUuid, string $costCenterUuid, int $idCompany): ?BudgetTransfer
    {
        $costCenterTransfer = $this->costCenterReader->findCostCenter($costCenterUuid, $idCompany);

        if (!$costCenterTransfer) {
            return null;
        }

        $budgetCollectionTransfer = $this->purchasingControlClient->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addUuid($budgetUuid)
                    ->addIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
                    ->setWithBudgetConsumption(true),
            ),
        );

        return $budgetCollectionTransfer->getBudgets()->getIterator()->current() ?: null;
    }
}

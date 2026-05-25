<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use ArrayObject;
use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;

class CostCenterBudgetExpander implements CostCenterBudgetExpanderInterface
{
    public function __construct(protected readonly BudgetReaderInterface $budgetReader)
    {
    }

    public function expandWithBudgets(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        CostCenterCriteriaTransfer $costCenterCriteriaTransfer,
    ): CostCenterCollectionTransfer {
        $costCenterIds = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $costCenterIds[] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        if ($costCenterIds === []) {
            return $costCenterCollectionTransfer;
        }

        $budgetConditionsTransfer = (new BudgetConditionsTransfer())
            ->setActiveOnDate($costCenterCriteriaTransfer->getCostCenterConditions()?->getBudgetActiveOnDate())
            ->setCostCenterIds($costCenterIds)
            ->setCurrencyIsoCodes($costCenterCriteriaTransfer->getCostCenterConditions()?->getCurrencyIsoCodes() ?? [])
            ->setWithBudgetConsumption(true);

        if (!$costCenterCriteriaTransfer->getCostCenterConditions()?->getWithInactiveBudgets()) {
            $budgetConditionsTransfer->setIsActive(true);
        }

        $budgetCollectionTransfer = $this->budgetReader->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions($budgetConditionsTransfer),
        );

        /** @var array<int, list<\Generated\Shared\Transfer\BudgetTransfer>> $budgetTransfersByCostCenterId */
        $budgetTransfersByCostCenterId = [];

        foreach ($budgetCollectionTransfer->getBudgets() as $budgetTransfer) {
            $budgetTransfersByCostCenterId[$budgetTransfer->getIdCostCenterOrFail()][] = $budgetTransfer;
        }

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $costCenterTransfer->setBudgets(
                new ArrayObject($budgetTransfersByCostCenterId[$costCenterTransfer->getIdCostCenterOrFail()] ?? []),
            );
        }

        return $costCenterCollectionTransfer;
    }
}

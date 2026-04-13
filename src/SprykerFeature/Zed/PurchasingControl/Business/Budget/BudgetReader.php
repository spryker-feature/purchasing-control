<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class BudgetReader implements BudgetReaderInterface
{
    public function __construct(protected PurchasingControlRepositoryInterface $costCenterRepository)
    {
    }

    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer
    {
        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setCurrencyIsoCode($currencyIsoCode)
            ->setIsActive(true)
            ->setActiveOnDate(date('Y-m-d'));

        return $this->costCenterRepository->findBudgetCollection($budgetCriteriaTransfer);
    }

    public function getBudgetById(int $idBudget): BudgetTransfer
    {
        $budgetTransfer = $this->costCenterRepository->findBudgetById($idBudget);

        if ($budgetTransfer === null) {
            return new BudgetTransfer();
        }

        return $budgetTransfer;
    }
}

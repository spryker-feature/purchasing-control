<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetConsumptionCollectionTransfer;
use Generated\Shared\Transfer\BudgetConsumptionCriteriaTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;

interface PurchasingControlRepositoryInterface
{
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;

    public function getBudgetConsumptionCollection(BudgetConsumptionCriteriaTransfer $budgetConsumptionCriteriaTransfer): BudgetConsumptionCollectionTransfer;

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int>
     */
    public function getCompanyBusinessUnitIdsForCompany(int $idCompany, array $companyBusinessUnitIds): array;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;

interface PurchasingControlRepositoryInterface
{
    /**
     * @api
     */
    public function findCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * @api
     */
    public function findCostCenterById(int $idCostCenter): ?CostCenterTransfer;

    /**
     * @api
     */
    public function findBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;

    /**
     * @api
     */
    public function findBudgetById(int $idBudget): ?BudgetTransfer;
}

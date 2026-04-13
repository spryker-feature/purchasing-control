<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;

interface PurchasingControlEntityManagerInterface
{
    /**
     * @api
     */
    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer;

    /**
     * @api
     */
    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer;

    /**
     * @api
     */
    public function createBudget(BudgetTransfer $budgetTransfer): BudgetTransfer;

    /**
     * @api
     */
    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetTransfer;

    /**
     * @api
     */
    public function createBudgetConsumption(BudgetConsumptionTransfer $budgetConsumptionTransfer): BudgetConsumptionTransfer;

    /**
     * @api
     */
    public function deleteBudgetConsumptionByIdSalesOrder(int $idSalesOrder): void;

    /**
     * @api
     */
    public function updateSalesOrderCostCenter(int $idSalesOrder, int $idCostCenter, ?int $idBudget): void;
}

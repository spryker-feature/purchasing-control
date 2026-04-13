<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl\Zed;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;

interface PurchasingControlStubInterface
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getActiveCostCentersForCompanyBusinessUnitAction()
     */
    public function getActiveCostCentersForCompanyBusinessUnit(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getActiveBudgetsForCostCenterAction()
     */
    public function getActiveBudgetsForCostCenter(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;
}

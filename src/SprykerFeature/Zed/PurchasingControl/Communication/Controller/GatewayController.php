<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class GatewayController extends AbstractGatewayController
{
    public function getActiveCostCentersForCompanyBusinessUnitAction(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        return $this->getFacade()->getCostCenterCollection($costCenterCriteriaTransfer);
    }

    public function getActiveBudgetsForCostCenterAction(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        return $this->getFacade()->getActiveBudgetsForCostCenter(
            $budgetCriteriaTransfer->getIdCostCenterOrFail(),
            $budgetCriteriaTransfer->getCurrencyIsoCodeOrFail(),
        );
    }
}

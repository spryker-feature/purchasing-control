<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class GatewayController extends AbstractGatewayController
{
    public function getCostCenterCollectionAction(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        return $this->getFacade()->getCostCenterCollection($costCenterCriteriaTransfer);
    }

    public function getBudgetCollectionAction(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        return $this->getFacade()->getBudgetCollection($budgetCriteriaTransfer);
    }

    public function createCostCenterCollectionAction(
        CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer
    ): CostCenterCollectionResponseTransfer {
        return $this->getFacade()->createCostCenterCollection($costCenterCollectionRequestTransfer);
    }

    public function updateCostCenterCollectionAction(
        CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer
    ): CostCenterCollectionResponseTransfer {
        return $this->getFacade()->updateCostCenterCollection($costCenterCollectionRequestTransfer);
    }

    public function updateQuoteCostCenterAction(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer
    {
        return $this->getFacade()->updateQuoteCostCenter($requestTransfer);
    }

    public function createBudgetCollectionAction(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        return $this->getFacade()->createBudgetCollection($budgetCollectionRequestTransfer);
    }

    public function updateBudgetCollectionAction(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        return $this->getFacade()->updateBudgetCollection($budgetCollectionRequestTransfer);
    }
}

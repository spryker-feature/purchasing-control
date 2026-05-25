<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl\Zed;

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

interface PurchasingControlStubInterface
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getCostCenterCollectionAction()
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getBudgetCollectionAction()
     */
    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::createCostCenterCollectionAction()
     */
    public function createCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateCostCenterCollectionAction()
     */
    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateQuoteCostCenterAction()
     */
    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::createBudgetCollectionAction()
     */
    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateBudgetCollectionAction()
     */
    public function updateBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;
}

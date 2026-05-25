<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl;

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
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \SprykerFeature\Client\PurchasingControl\PurchasingControlFactory getFactory()
 */
class PurchasingControlClient extends AbstractClient implements PurchasingControlClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->getCostCenterCollection($costCenterCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->getBudgetCollection($budgetCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->updateQuoteCostCenter($requestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->createBudgetCollection($budgetCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        return $this->getFactory()
            ->createPurchasingControlStub()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);
    }
}

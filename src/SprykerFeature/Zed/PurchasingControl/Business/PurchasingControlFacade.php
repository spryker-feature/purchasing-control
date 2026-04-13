<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetResponseTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterResponseTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface getRepository()
 */
class PurchasingControlFacade extends AbstractFacade implements PurchasingControlFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer
    {
        return $this->getFactory()
            ->createCostCenterWriter()
            ->createCostCenter($costCenterTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer
    {
        return $this->getFactory()
            ->createCostCenterWriter()
            ->updateCostCenter($costCenterTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        return $this->getFactory()
            ->createCostCenterReader()
            ->getCostCenterCollection($costCenterCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function createBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer
    {
        return $this->getFactory()
            ->createBudgetWriter()
            ->createBudget($budgetTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer
    {
        return $this->getFactory()
            ->createBudgetWriter()
            ->updateBudget($budgetTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer
    {
        return $this->getFactory()
            ->createBudgetReader()
            ->getActiveBudgetsForCostCenter($idCostCenter, $currencyIsoCode);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getCostCenterById(int $idCostCenter): CostCenterTransfer
    {
        return $this->getFactory()
            ->createCostCenterReader()
            ->getCostCenterById($idCostCenter);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getBudgetById(int $idBudget): BudgetTransfer
    {
        return $this->getFactory()
            ->createBudgetReader()
            ->getBudgetById($idBudget);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function saveCostCenterToOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->getFactory()
            ->createCostCenterOrderSaver()
            ->saveCostCenterToOrder($quoteTransfer, $saveOrderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function expandQuoteWithDefaultCostCenter(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createCostCenterQuoteExpander()
            ->expand($quoteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function validateBudgetForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        return $this->getFactory()
            ->createBudgetValidator()
            ->validateBudgetForCheckout($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function consumeBudget(int $idBudget, int $idSalesOrder, int $amountInCents): void
    {
        $this->getFactory()
            ->createBudgetConsumptionWriter()
            ->consumeBudget($idBudget, $idSalesOrder, $amountInCents);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function restoreBudget(int $idSalesOrder): void
    {
        $this->getFactory()
            ->createBudgetConsumptionWriter()
            ->restoreBudget($idSalesOrder);
    }
}

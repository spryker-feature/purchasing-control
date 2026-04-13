<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterQuoteExpander implements CostCenterQuoteExpanderInterface
{
    public function __construct(protected readonly PurchasingControlRepositoryInterface $costCenterRepository)
    {
    }

    public function expand(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        $idCompanyBusinessUnit = $quoteTransfer->getCustomer()
            ?->getCompanyUserTransfer()
            ?->getFkCompanyBusinessUnit();

        if ($idCompanyBusinessUnit === null) {
            return $quoteTransfer;
        }

        $currencyIsoCode = $quoteTransfer->getCurrency()?->getCode() ?? '';

        $quoteTransfer = $this->expandWithDefaultCostCenter($quoteTransfer, $idCompanyBusinessUnit, $currencyIsoCode);

        if ($quoteTransfer->getIdCostCenter() === null) {
            return $quoteTransfer;
        }

        return $this->expandWithFirstActiveBudget($quoteTransfer, $currencyIsoCode);
    }

    protected function expandWithDefaultCostCenter(QuoteTransfer $quoteTransfer, int $idCompanyBusinessUnit, string $currencyIsoCode): QuoteTransfer
    {
        if ($quoteTransfer->getIdCostCenter() !== null) {
            return $quoteTransfer;
        }

        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->setCurrencyIsoCode($currencyIsoCode);

        $costCenters = $this->costCenterRepository
            ->findCostCenterCollection($criteriaTransfer)
            ->getCostCenters();

        if ($costCenters->count() !== 1) {
            return $quoteTransfer;
        }

        $quoteTransfer->setIdCostCenter($costCenters[0]->getIdCostCenterOrFail());

        return $quoteTransfer;
    }

    protected function expandWithFirstActiveBudget(QuoteTransfer $quoteTransfer, string $currencyIsoCode): QuoteTransfer
    {
        if ($quoteTransfer->getIdBudget() !== null) {
            return $quoteTransfer;
        }

        $criteriaTransfer = (new BudgetCriteriaTransfer())
            ->setIdCostCenter($quoteTransfer->getIdCostCenterOrFail())
            ->setCurrencyIsoCode($currencyIsoCode)
            ->setIsActive(true)
            ->setActiveOnDate(date('Y-m-d'));

        $budgets = $this->costCenterRepository
            ->findBudgetCollection($criteriaTransfer)
            ->getBudgets();

        $firstBudget = $budgets->getIterator()->current();
        if ($firstBudget) {
            $quoteTransfer->setIdBudget($firstBudget->getIdBudgetOrFail());
        }

        return $quoteTransfer;
    }
}

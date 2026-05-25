<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterQuoteExpander implements CostCenterQuoteExpanderInterface
{
    /**
     * @var array<string, int|null>
     */
    protected static array $companyBusinessUnitIdCache = [];

    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $costCenterRepository,
        protected readonly CompanyUserFacadeInterface $companyUserFacade,
    ) {
    }

    public function expand(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        $idCompanyBusinessUnit = $quoteTransfer->getCustomer()
            ?->getCompanyUserTransfer()
            ?->getFkCompanyBusinessUnit();

        if ($idCompanyBusinessUnit === null) {
            $idCompanyBusinessUnit = $this->resolveCompanyBusinessUnitIdByCustomerReference($quoteTransfer->getCustomerReference());
        }

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

        $conditionsTransfer = (new CostCenterConditionsTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->addCurrencyIsoCode($currencyIsoCode);

        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions($conditionsTransfer);

        $costCenterTransfers = $this->costCenterRepository
            ->getCostCenterCollection($criteriaTransfer)
            ->getCostCenters();

        if ($costCenterTransfers->count() !== 1) {
            return $quoteTransfer;
        }

        $quoteTransfer->setIdCostCenter($costCenterTransfers[0]->getIdCostCenterOrFail());

        return $quoteTransfer;
    }

    protected function expandWithFirstActiveBudget(QuoteTransfer $quoteTransfer, string $currencyIsoCode): QuoteTransfer
    {
        if ($quoteTransfer->getIdBudget() !== null) {
            return $quoteTransfer;
        }

        $criteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addIdCostCenter($quoteTransfer->getIdCostCenterOrFail())
                    ->addCurrencyIsoCode($currencyIsoCode)
                    ->setIsActive(true)
                    ->setActiveOnDate(date('Y-m-d'))
                    ->setWithBudgetConsumption(true),
            );

        $budgets = $this->costCenterRepository
            ->getBudgetCollection($criteriaTransfer)
            ->getBudgets();

        $firstBudget = $budgets->getIterator()->current();
        if ($firstBudget) {
            $quoteTransfer->setIdBudget($firstBudget->getIdBudgetOrFail());
        }

        return $quoteTransfer;
    }

    protected function resolveCompanyBusinessUnitIdByCustomerReference(?string $customerReference): ?int
    {
        if ($customerReference === null) {
            return null;
        }

        if (array_key_exists($customerReference, static::$companyBusinessUnitIdCache)) {
            return static::$companyBusinessUnitIdCache[$customerReference];
        }

        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference($customerReference);
        $companyUserCollection = $this->companyUserFacade->getActiveCompanyUsersByCustomerReference($customerTransfer);
        $firstCompanyUser = $companyUserCollection->getCompanyUsers()->getIterator()->current();
        static::$companyBusinessUnitIdCache[$customerReference] = $firstCompanyUser?->getFkCompanyBusinessUnit();

        return static::$companyBusinessUnitIdCache[$customerReference];
    }
}

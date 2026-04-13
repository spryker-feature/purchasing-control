<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\QuoteApproval\Business\QuoteApprovalFacadeInterface;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class BudgetValidator implements BudgetValidatorInterface
{
    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $costCenterRepository,
        protected readonly QuoteApprovalFacadeInterface $quoteApprovalFacade,
    ) {
    }

    public function validateBudgetForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        if ($quoteTransfer->getIdBudget() === null) {
            return $this->validateBudgetIsRequired($quoteTransfer, $checkoutResponseTransfer);
        }

        $budgetTransfer = $this->costCenterRepository->findBudgetById($quoteTransfer->getIdBudgetOrFail());

        if ($budgetTransfer === null) {
            return $this->validateBudgetIsRequired($quoteTransfer, $checkoutResponseTransfer);
        }

        // If the cost center was deactivated after budget selection, treat the stale selection as cleared
        if (!$this->isCostCenterActiveForCustomer($budgetTransfer, $quoteTransfer)) {
            return $this->validateBudgetIsRequired($quoteTransfer, $checkoutResponseTransfer);
        }

        $grandTotal = $quoteTransfer->getTotals() ? ($quoteTransfer->getTotalsOrFail()->getGrandTotal() ?? 0) : 0;

        if ($grandTotal <= $budgetTransfer->getRemainingAmount()) {
            return true;
        }

        $enforcementRule = $budgetTransfer->getEnforcementRuleOrFail();

        if ($enforcementRule === PurchasingControlConfig::ENFORCEMENT_RULE_WARN) {
            $checkoutResponseTransfer->addError(
                (new CheckoutErrorTransfer())->setMessage('purchasing_control.validation.warn'),
            );

            return true;
        }

        if ($enforcementRule === PurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL) {
            // Approved state: approval row exists but is no longer waiting (i.e. was approved)
            if (
                $this->quoteApprovalFacade->isQuoteInApprovalProcess($quoteTransfer)
                && !$this->quoteApprovalFacade->isQuoteWaitingForApproval($quoteTransfer)
            ) {
                return true;
            }

            $checkoutResponseTransfer->addError(
                (new CheckoutErrorTransfer())->setMessage('purchasing_control.validation.require-approval'),
            );

            return false;
        }

        $checkoutResponseTransfer->addError(
            (new CheckoutErrorTransfer())->setMessage('purchasing_control.validation.block'),
        );

        return false;
    }

    protected function isCostCenterActiveForCustomer(BudgetTransfer $budgetTransfer, QuoteTransfer $quoteTransfer): bool
    {
        $idCompanyBusinessUnit = $quoteTransfer->getCustomer()
            ?->getCompanyUserTransfer()
            ?->getFkCompanyBusinessUnit();

        if ($idCompanyBusinessUnit === null) {
            return true;
        }

        $currencyIsoCode = $quoteTransfer->getCurrency()?->getCode() ?? '';
        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->setCurrencyIsoCode($currencyIsoCode);

        $activeCostCenters = $this->costCenterRepository
            ->findCostCenterCollection($criteriaTransfer)
            ->getCostCenters();

        foreach ($activeCostCenters as $costCenter) {
            if ($costCenter->getIdCostCenter() === $budgetTransfer->getIdCostCenter()) {
                return true;
            }
        }

        return false;
    }

    protected function validateBudgetIsRequired(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $idCompanyBusinessUnit = $quoteTransfer->getCustomer()
            ?->getCompanyUserTransfer()
            ?->getFkCompanyBusinessUnit();

        if ($idCompanyBusinessUnit === null) {
            return true;
        }

        $currencyIsoCode = $quoteTransfer->getCurrency()?->getCode() ?? '';
        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->setCurrencyIsoCode($currencyIsoCode);

        $hasCostCenters = $this->costCenterRepository
            ->findCostCenterCollection($criteriaTransfer)
            ->getCostCenters()
            ->count() > 0;

        if (!$hasCostCenters) {
            return true;
        }

        $checkoutResponseTransfer->addError(
            (new CheckoutErrorTransfer())->setMessage('purchasing_control.validation.required'),
        );

        return false;
    }
}

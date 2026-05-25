<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use InvalidArgumentException;
use Spryker\Zed\QuoteApproval\Business\QuoteApprovalFacadeInterface;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterActiveCheckerInterface;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class BudgetCheckoutValidator implements BudgetCheckoutValidatorInterface
{
    protected const string GLOSSARY_KEY_VALIDATION_WARN = 'purchasing_control.validation.warn';

    protected const string GLOSSARY_KEY_VALIDATION_REQUIRE_APPROVAL = 'purchasing_control.validation.require-approval';

    protected const string GLOSSARY_KEY_VALIDATION_BLOCK = 'purchasing_control.validation.block';

    protected const string GLOSSARY_KEY_VALIDATION_REQUIRED = 'purchasing_control.validation.required';

    protected const string GLOSSARY_KEY_VALIDATION_INACTIVE_COST_CENTER = 'purchasing_control.validation.inactive-cost-center';

    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $purchasingControlRepository,
        protected readonly QuoteApprovalFacadeInterface $quoteApprovalFacade,
        protected readonly CostCenterActiveCheckerInterface $costCenterActiveChecker,
    ) {
    }

    public function validateBudgetForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $budgetTransfer = $this->findBudgetForQuote($quoteTransfer);

        if ($budgetTransfer === null || !$this->isBudgetActiveForQuote($budgetTransfer, $quoteTransfer)) {
            return $this->validateBudgetIsRequired($quoteTransfer, $checkoutResponseTransfer);
        }

        if ($this->getGrandTotal($quoteTransfer) <= $budgetTransfer->getRemainingAmount()) {
            return true;
        }

        return $this->handleExceededBudget($budgetTransfer, $quoteTransfer, $checkoutResponseTransfer);
    }

    protected function findBudgetForQuote(QuoteTransfer $quoteTransfer): ?BudgetTransfer
    {
        if ($quoteTransfer->getIdBudget() === null) {
            return null;
        }

        $budgetCollectionTransfer = $this->purchasingControlRepository->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addIdBudget($quoteTransfer->getIdBudgetOrFail())
                    ->setWithBudgetConsumption(true),
            ),
        );

        return $budgetCollectionTransfer->getBudgets()->getIterator()->current() ?: null;
    }

    protected function isBudgetActiveForQuote(BudgetTransfer $budgetTransfer, QuoteTransfer $quoteTransfer): bool
    {
        return $budgetTransfer->getIsActive() && $this->costCenterActiveChecker->isCostCenterActiveForQuote($budgetTransfer, $quoteTransfer);
    }

    protected function getGrandTotal(QuoteTransfer $quoteTransfer): int
    {
        return $quoteTransfer->getTotals()?->getGrandTotal() ?? 0;
    }

    protected function handleExceededBudget(
        BudgetTransfer $budgetTransfer,
        QuoteTransfer $quoteTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer,
    ): bool {
        return match ($budgetTransfer->getEnforcementRuleOrFail()) {
            PurchasingControlConfig::ENFORCEMENT_RULE_WARN => $this->handleWarnRule($checkoutResponseTransfer),
            PurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL => $this->handleRequireApprovalRule($quoteTransfer, $checkoutResponseTransfer),
            PurchasingControlConfig::ENFORCEMENT_RULE_BLOCK => $this->failWithCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_VALIDATION_BLOCK),
            default => throw new InvalidArgumentException(sprintf('Unknown enforcement rule: %s', $budgetTransfer->getEnforcementRuleOrFail())),
        };
    }

    protected function handleWarnRule(CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $checkoutResponseTransfer->addError(
            (new CheckoutErrorTransfer())->setMessage(static::GLOSSARY_KEY_VALIDATION_WARN),
        );

        return true;
    }

    protected function handleRequireApprovalRule(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        if (
            $this->quoteApprovalFacade->isQuoteInApprovalProcess($quoteTransfer)
            && !$this->quoteApprovalFacade->isQuoteWaitingForApproval($quoteTransfer)
        ) {
            return true;
        }

        return $this->failWithCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_VALIDATION_REQUIRE_APPROVAL);
    }

    protected function validateBudgetIsRequired(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        if (!$this->costCenterActiveChecker->hasActiveCostCentersForQuote($quoteTransfer)) {
            if ($quoteTransfer->getIdCostCenter() !== null) {
                return $this->failWithCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_VALIDATION_INACTIVE_COST_CENTER);
            }

            return true;
        }

        return $this->failWithCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_VALIDATION_REQUIRED);
    }

    protected function failWithCheckoutError(CheckoutResponseTransfer $checkoutResponseTransfer, string $glossaryKey): bool
    {
        $checkoutResponseTransfer->addError(
            (new CheckoutErrorTransfer())->setMessage($glossaryKey),
        );
        $checkoutResponseTransfer->setIsSuccess(false);

        return false;
    }
}

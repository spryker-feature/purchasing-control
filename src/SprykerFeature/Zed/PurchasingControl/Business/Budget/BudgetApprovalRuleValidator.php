<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;

class BudgetApprovalRuleValidator implements BudgetApprovalRuleValidatorInterface
{
    protected const string GLOSSARY_KEY_VALIDATION_APPROVAL_RULE_NOT_SUPPORTED = 'purchasing_control.validation.approval-rule-not-supported';

    public function __construct(protected readonly QuoteBudgetReaderInterface $quoteBudgetReader)
    {
    }

    public function validateBudgetApprovalRule(QuoteTransfer $quoteTransfer): ?CheckoutErrorTransfer
    {
        $budgetTransfer = $this->quoteBudgetReader->findBudgetForQuote($quoteTransfer);

        if ($budgetTransfer?->getEnforcementRule() !== PurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL) {
            return null;
        }

        return (new CheckoutErrorTransfer())
            ->setMessage(static::GLOSSARY_KEY_VALIDATION_APPROVAL_RULE_NOT_SUPPORTED);
    }
}

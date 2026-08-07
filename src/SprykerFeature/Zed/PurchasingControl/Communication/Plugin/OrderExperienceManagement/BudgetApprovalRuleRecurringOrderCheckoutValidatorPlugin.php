<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\OrderExperienceManagement;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Zed\OrderExperienceManagement\Dependency\Plugin\RecurringOrderCheckoutValidatorPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin extends AbstractPlugin implements RecurringOrderCheckoutValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns a `CheckoutErrorTransfer` when the budget selected on the quote has the `require_approval` enforcement rule.
     * - A recurring order places its follow-up orders unattended, so no approval can be granted for them upfront.
     * - Applies regardless of the quote grand total and of the remaining budget amount.
     * - Returns `null` when no budget is selected on the quote or the budget uses another enforcement rule.
     *
     * @api
     */
    public function validate(QuoteTransfer $quoteTransfer): ?CheckoutErrorTransfer
    {
        return $this->getBusinessFactory()
            ->createBudgetApprovalRuleValidator()
            ->validateBudgetApprovalRule($quoteTransfer);
    }
}

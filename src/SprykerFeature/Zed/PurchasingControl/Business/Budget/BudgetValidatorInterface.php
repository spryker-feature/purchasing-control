<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface BudgetValidatorInterface
{
    /**
     * Validates the selected budget against the quote grand total.
     * Returns true when no budget is selected, grand total is within remaining budget,
     * or enforcement rule is 'warn' (warning added to response but checkout proceeds).
     * Returns false and adds a checkout error when enforcement rule is 'block' and budget is exceeded,
     * or when rule is 'require_approval' and the quote has not yet been approved.
     */
    public function validateBudgetForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool;
}

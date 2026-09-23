<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\OrderIntakeRequestTransfer;
use Generated\Shared\Transfer\OrderIntakeResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface BudgetOrderIntakeQuoteExpanderInterface
{
    public function expand(
        OrderIntakeRequestTransfer $orderIntakeRequestTransfer,
        QuoteTransfer $quoteTransfer,
        OrderIntakeResponseTransfer $orderIntakeResponseTransfer,
    ): QuoteTransfer;
}

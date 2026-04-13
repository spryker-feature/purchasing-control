<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\QuoteTransfer;

interface CostCenterQuoteExpanderInterface
{
    public function expand(QuoteTransfer $quoteTransfer): QuoteTransfer;
}

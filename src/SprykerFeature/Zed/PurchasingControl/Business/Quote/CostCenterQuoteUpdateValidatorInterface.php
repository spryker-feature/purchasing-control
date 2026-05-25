<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface CostCenterQuoteUpdateValidatorInterface
{
    public function validate(
        CostCenterQuoteUpdateRequestTransfer $costCenterQuoteUpdateRequestTransfer,
        QuoteTransfer $quoteTransfer
    ): CostCenterQuoteUpdateResponseTransfer;
}

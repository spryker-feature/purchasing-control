<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;

interface QuoteRequestReaderInterface
{
    public function findQuoteRequest(string $quoteRequestReference): QuoteRequestResponseTransfer;
}

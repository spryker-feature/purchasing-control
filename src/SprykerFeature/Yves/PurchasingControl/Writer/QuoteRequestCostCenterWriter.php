<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Writer;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Spryker\Client\QuoteRequest\QuoteRequestClientInterface;

class QuoteRequestCostCenterWriter extends AbstractQuoteRequestCostCenterWriter
{
    public function __construct(protected readonly QuoteRequestClientInterface $quoteRequestClient)
    {
    }

    protected function isQuoteRequestEditable(QuoteRequestTransfer $quoteRequestTransfer): bool
    {
        return $this->quoteRequestClient->isQuoteRequestEditable($quoteRequestTransfer);
    }

    protected function updateQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestClient->updateQuoteRequest($quoteRequestTransfer);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Writer;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface;

class AgentQuoteRequestCostCenterWriter extends AbstractQuoteRequestCostCenterWriter
{
    public function __construct(protected readonly QuoteRequestAgentClientInterface $quoteRequestAgentClient)
    {
    }

    protected function isQuoteRequestEditable(QuoteRequestTransfer $quoteRequestTransfer): bool
    {
        return $this->quoteRequestAgentClient->isQuoteRequestEditable($quoteRequestTransfer);
    }

    protected function updateQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestAgentClient->updateQuoteRequest($quoteRequestTransfer);
    }
}

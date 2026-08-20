<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Client\QuoteRequestAgent\QuoteRequestAgentClientInterface;

class AgentQuoteRequestReader implements QuoteRequestReaderInterface
{
    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\Reader\QuoteRequestReader::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS
     */
    protected const string GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS = 'quote_request.validation.error.not_exists';

    public function __construct(protected readonly QuoteRequestAgentClientInterface $quoteRequestAgentClient)
    {
    }

    public function findQuoteRequest(string $quoteRequestReference): QuoteRequestResponseTransfer
    {
        $quoteRequestTransfer = $this->quoteRequestAgentClient->findQuoteRequestByReference($quoteRequestReference);

        if ($quoteRequestTransfer === null) {
            return (new QuoteRequestResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new MessageTransfer())->setValue(static::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EXISTS));
        }

        return (new QuoteRequestResponseTransfer())
            ->setIsSuccessful(true)
            ->setQuoteRequest($quoteRequestTransfer);
    }
}

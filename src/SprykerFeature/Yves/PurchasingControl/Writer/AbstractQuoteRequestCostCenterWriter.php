<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Writer;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;

abstract class AbstractQuoteRequestCostCenterWriter implements QuoteRequestCostCenterWriterInterface
{
    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\Writer\QuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS
     */
    protected const string GLOSSARY_KEY_QUOTE_REQUEST_NOT_EDITABLE = 'quote_request.validation.error.wrong_status';

    abstract protected function isQuoteRequestEditable(QuoteRequestTransfer $quoteRequestTransfer): bool;

    abstract protected function updateQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer;

    public function updateCostCenter(
        QuoteRequestTransfer $quoteRequestTransfer,
        ?int $idCostCenter,
        ?int $idBudget
    ): QuoteRequestResponseTransfer {
        if (!$this->isQuoteRequestEditable($quoteRequestTransfer)) {
            return $this->createErrorResponse(static::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EDITABLE);
        }

        $quoteRequestTransfer->getLatestVersionOrFail()
            ->getQuoteOrFail()
            ->setIdCostCenter($idCostCenter)
            ->setIdBudget($idBudget);

        return $this->updateQuoteRequest($quoteRequestTransfer);
    }

    protected function createErrorResponse(string $glossaryKey): QuoteRequestResponseTransfer
    {
        return (new QuoteRequestResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage((new MessageTransfer())->setValue($glossaryKey));
    }
}

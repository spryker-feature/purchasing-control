<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Quote\Business\QuoteFacadeInterface;

class CostCenterQuoteUpdater implements CostCenterQuoteUpdaterInterface
{
    protected const string GLOSSARY_KEY_QUOTE_NOT_FOUND = 'purchasing_control.error.quote_not_found';

    public function __construct(
        protected readonly QuoteFacadeInterface $quoteFacade,
        protected readonly CostCenterQuoteUpdateValidatorInterface $validator,
    ) {
    }

    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer
    {
        $quoteResponseTransfer = $this->quoteFacade->findQuoteById($requestTransfer->getIdQuoteOrFail());
        if (!$quoteResponseTransfer->getIsSuccessful() || $quoteResponseTransfer->getQuoteTransfer() === null) {
            return (new CostCenterQuoteUpdateResponseTransfer())
                ->setIsSuccessful(false)
                ->addError((new ErrorTransfer())->setMessage(static::GLOSSARY_KEY_QUOTE_NOT_FOUND));
        }

        $costCenterQuoteUpdateResponseTransfer = $this->validator->validate($requestTransfer, $quoteResponseTransfer->getQuoteTransferOrFail());
        if (!$costCenterQuoteUpdateResponseTransfer->getIsSuccessful()) {
            return $costCenterQuoteUpdateResponseTransfer;
        }

        $quoteTransfer = $this->applyRequestToQuote($requestTransfer, $quoteResponseTransfer->getQuoteTransferOrFail());

        $quoteResponseTransfer = $this->quoteFacade->updateQuote($quoteTransfer);

        return $this->mapQuoteResponseToUpdateResponse($quoteResponseTransfer, new CostCenterQuoteUpdateResponseTransfer());
    }

    protected function applyRequestToQuote(
        CostCenterQuoteUpdateRequestTransfer $costCenterQuoteUpdateRequestTransfer,
        QuoteTransfer $quoteTransfer,
    ): QuoteTransfer {
        return $quoteTransfer
            ->setCustomer($costCenterQuoteUpdateRequestTransfer->getCustomerOrFail())
            ->setIdCostCenter($costCenterQuoteUpdateRequestTransfer->getIdCostCenterOrFail())
            ->setIdBudget($costCenterQuoteUpdateRequestTransfer->getIdBudget());
    }

    protected function mapQuoteResponseToUpdateResponse(
        QuoteResponseTransfer $quoteResponseTransfer,
        CostCenterQuoteUpdateResponseTransfer $costCenterQuoteUpdateResponseTransfer,
    ): CostCenterQuoteUpdateResponseTransfer {
        $costCenterQuoteUpdateResponseTransfer->setIsSuccessful($quoteResponseTransfer->getIsSuccessful() ?? false);

        foreach ($quoteResponseTransfer->getErrors() as $quoteErrorTransfer) {
            $costCenterQuoteUpdateResponseTransfer->addError(
                (new ErrorTransfer())->setMessage($quoteErrorTransfer->getMessage()),
            );
        }

        if ($quoteResponseTransfer->getIsSuccessful()) {
            $costCenterQuoteUpdateResponseTransfer->setQuote($quoteResponseTransfer->getQuoteTransfer());
        }

        return $costCenterQuoteUpdateResponseTransfer;
    }
}

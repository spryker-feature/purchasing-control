<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Client\CompanyUser\CompanyUserClientInterface;
use Spryker\Client\QuoteRequest\QuoteRequestClientInterface;

class CompanyUserQuoteRequestReader implements QuoteRequestReaderInterface
{
    protected const string GLOSSARY_KEY_NOT_COMPANY_USER = 'purchasing_control.error.not_company_user';

    public function __construct(
        protected readonly CompanyUserClientInterface $companyUserClient,
        protected readonly QuoteRequestClientInterface $quoteRequestClient,
    ) {
    }

    public function findQuoteRequest(string $quoteRequestReference): QuoteRequestResponseTransfer
    {
        $idCompanyUser = $this->companyUserClient->findCompanyUser()?->getIdCompanyUser();

        if ($idCompanyUser === null) {
            return (new QuoteRequestResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new MessageTransfer())->setValue(static::GLOSSARY_KEY_NOT_COMPANY_USER));
        }

        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setQuoteRequestReference($quoteRequestReference)
            ->setIdCompanyUser($idCompanyUser);

        return $this->quoteRequestClient->getQuoteRequest($quoteRequestFilterTransfer);
    }
}

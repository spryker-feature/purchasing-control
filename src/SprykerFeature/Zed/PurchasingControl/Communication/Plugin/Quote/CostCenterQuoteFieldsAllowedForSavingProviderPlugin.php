<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Quote;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\QuoteExtension\Dependency\Plugin\QuoteFieldsAllowedForSavingProviderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterQuoteFieldsAllowedForSavingProviderPlugin extends AbstractPlugin implements QuoteFieldsAllowedForSavingProviderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return list<string>
     */
    public function execute(QuoteTransfer $quoteTransfer): array
    {
        return [QuoteTransfer::ID_BUDGET, QuoteTransfer::ID_COST_CENTER];
    }
}

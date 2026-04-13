<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Quote;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\QuoteExtension\Dependency\Plugin\QuoteExpanderPluginInterface;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterQuoteExpanderPlugin extends AbstractPlugin implements QuoteExpanderPluginInterface
{
    public function expand(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFacade()->expandQuoteWithDefaultCostCenter($quoteTransfer);
    }
}

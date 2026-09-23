<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\OrderExperienceManagement;

use Generated\Shared\Transfer\OrderIntakeRequestTransfer;
use Generated\Shared\Transfer\OrderIntakeResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Zed\OrderExperienceManagement\Dependency\Plugin\OrderIntakeQuoteExpanderPluginInterface;

/**
 * Charges an intaken order against a PurchasingControl budget.
 *
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getBusinessFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetOrderIntakeQuoteExpanderPlugin extends AbstractPlugin implements OrderIntakeQuoteExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function expandQuote(
        OrderIntakeRequestTransfer $orderIntakeRequestTransfer,
        QuoteTransfer $quoteTransfer,
        OrderIntakeResponseTransfer $orderIntakeResponseTransfer,
    ): QuoteTransfer {
        return $this->getBusinessFactory()
            ->createBudgetOrderIntakeQuoteExpander()
            ->expand($orderIntakeRequestTransfer, $quoteTransfer, $orderIntakeResponseTransfer);
    }
}

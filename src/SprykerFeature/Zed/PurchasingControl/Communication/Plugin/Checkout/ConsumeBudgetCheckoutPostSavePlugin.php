<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Checkout;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutPostSaveInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 */
class ConsumeBudgetCheckoutPostSavePlugin extends AbstractPlugin implements CheckoutPostSaveInterface
{
    /**
     * {@inheritDoc}
     * - Does nothing when no budget is selected on the quote.
     * - Reads the grand total from the quote totals.
     * - Records budget consumption immediately after the order is saved so remaining balance
     *   is accurate for concurrent buyers from the moment the order is placed.
     *
     * @api
     */
    public function executeHook(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): void
    {
        $idBudget = $quoteTransfer->getIdBudget();

        if ($idBudget === null) {
            return;
        }

        $grandTotal = $quoteTransfer->getTotals() !== null
            ? ($quoteTransfer->getTotalsOrFail()->getGrandTotal() ?? 0)
            : 0;

        $idSalesOrder = $checkoutResponseTransfer->getSaveOrderOrFail()->getIdSalesOrderOrFail();

        $this->getFacade()->consumeBudget($idBudget, $idSalesOrder, $grandTotal);
    }
}

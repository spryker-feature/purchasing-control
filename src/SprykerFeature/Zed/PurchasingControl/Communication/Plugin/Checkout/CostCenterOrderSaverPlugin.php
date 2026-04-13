<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Checkout;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutDoSaveOrderInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * {@inheritDoc}
 *
 * @api
 *
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterOrderSaverPlugin extends AbstractPlugin implements CheckoutDoSaveOrderInterface
{
    public function saveOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->getFacade()->saveCostCenterToOrder($quoteTransfer, $saveOrderTransfer);
    }
}

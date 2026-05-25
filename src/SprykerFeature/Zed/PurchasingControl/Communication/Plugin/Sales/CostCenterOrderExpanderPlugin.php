<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrderExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getBusinessFactory()
 */
class CostCenterOrderExpanderPlugin extends AbstractPlugin implements OrderExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Does nothing when no cost center is assigned to the order.
     * - Expands OrderTransfer with CostCenter and company name resolved by the order company UUID.
     * - Expands OrderTransfer with Budget when a budget is assigned to the order.
     *
     * @api
     */
    public function hydrate(OrderTransfer $orderTransfer): OrderTransfer
    {
        return $this->getBusinessFactory()->createCostCenterOrderExpander()->expandOrder($orderTransfer);
    }
}

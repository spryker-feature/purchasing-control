<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getBusinessFactory()
 */
class CostCenterSearchOrderExpanderPlugin extends AbstractPlugin implements SearchOrderExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Expands each OrderTransfer in the list with CostCenterTransfer and BudgetTransfer when assigned.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     *
     * @return array<\Generated\Shared\Transfer\OrderTransfer>
     */
    public function expand(array $orderTransfers): array
    {
        return $this->getBusinessFactory()->createCostCenterOrderListExpander()->expandOrders($orderTransfers);
    }
}

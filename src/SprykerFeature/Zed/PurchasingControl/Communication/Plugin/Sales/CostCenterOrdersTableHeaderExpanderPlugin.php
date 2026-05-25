<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrdersTableHeaderExpanderPluginInterface;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class CostCenterOrdersTableHeaderExpanderPlugin extends AbstractPlugin implements OrdersTableHeaderExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Inserts a Cost Center column before the Actions column.
     *
     * @api
     *
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    public function expandHeaders(array $headers): array
    {
        $actionsColumn = $headers[SalesTablePluginInterface::ROW_ACTIONS] ?? null;
        unset($headers[SalesTablePluginInterface::ROW_ACTIONS]);

        $headers[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME] = 'Cost Center';

        if ($actionsColumn !== null) {
            $headers[SalesTablePluginInterface::ROW_ACTIONS] = $actionsColumn;
        }

        return $headers;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class CostCenterSalesTablePlugin extends AbstractPlugin implements SalesTablePluginInterface
{
    protected const string FALLBACK_VALUE = '-';

    /**
     * {@inheritDoc}
     * - Normalises the cost_center_name column to '-' when the order has no cost center assigned.
     *
     * @api
     *
     * @param callable $buttonGenerator
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    public function formatTableRow(callable $buttonGenerator, array $item): array
    {
        if (empty($item[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME])) {
            $item[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME] = static::FALLBACK_VALUE;
        }

        return $item;
    }
}

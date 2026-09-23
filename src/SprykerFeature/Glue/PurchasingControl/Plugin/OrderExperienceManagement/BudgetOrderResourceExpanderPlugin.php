<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\PurchasingControl\Plugin\OrderExperienceManagement;

use Generated\Api\Backend\Orders\OrdersBudgetBackendObject;
use Generated\Api\Backend\OrdersBackendResource;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Glue\Kernel\AbstractPlugin;
use SprykerFeature\Glue\OrderExperienceManagement\Dependency\Plugin\OrderResourceExpanderPluginInterface;

/**
 * Reports the budget an order was charged against on the Backend API `orders` resource.
 *
 * @see \SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrderExpanderPlugin
 */
class BudgetOrderResourceExpanderPlugin extends AbstractPlugin implements OrderResourceExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function expand(
        OrdersBackendResource $ordersBackendResource,
        OrderTransfer $orderTransfer,
    ): OrdersBackendResource {
        $ordersBackendResource->budget = $this->mapBudget($orderTransfer->getBudget());

        return $ordersBackendResource;
    }

    protected function mapBudget(?BudgetTransfer $budgetTransfer): ?OrdersBudgetBackendObject
    {
        if ($budgetTransfer === null) {
            return null;
        }

        return OrdersBudgetBackendObject::fromArray($budgetTransfer->toArray(true, true));
    }
}

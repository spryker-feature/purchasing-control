<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Generated\Shared\Transfer\OrderTableCriteriaTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrdersTableCriteriaFilterExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class CostCenterOrdersTableCriteriaFilterExpanderPlugin extends AbstractPlugin implements OrdersTableCriteriaFilterExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Filters orders by `OrderTableCriteriaTransfer.costCenterIds` when provided.
     * - Filters orders by `OrderTableCriteriaTransfer.budgetIds` when provided.
     *
     * @api
     */
    public function expandCriteria(SpySalesOrderQuery $salesOrderQuery, OrderTableCriteriaTransfer $orderTableCriteriaTransfer): SpySalesOrderQuery
    {
        if ($orderTableCriteriaTransfer->getCostCenterIds()) {
            $salesOrderQuery->filterByFkCostCenter_In($orderTableCriteriaTransfer->getCostCenterIds());
        }

        if ($orderTableCriteriaTransfer->getBudgetIds()) {
            $salesOrderQuery->filterByFkBudget_In($orderTableCriteriaTransfer->getBudgetIds());
        }

        return $salesOrderQuery;
    }
}

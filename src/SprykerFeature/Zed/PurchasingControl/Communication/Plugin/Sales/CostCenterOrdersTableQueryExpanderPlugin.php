<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Orm\Zed\PurchasingControl\Persistence\Map\SpyCostCenterTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrdersTableQueryExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class CostCenterOrdersTableQueryExpanderPlugin extends AbstractPlugin implements OrdersTableQueryExpanderPluginInterface
{
    public const string COL_COST_CENTER_NAME = 'cost_center_name';

    /**
     * {@inheritDoc}
     * - Adds a LEFT JOIN from spy_sales_order.fk_cost_center to spy_cost_center.id_cost_center.
     * - Adds spy_cost_center.name as virtual column cost_center_name to query results.
     *
     * @api
     */
    public function expandQuery(SpySalesOrderQuery $salesOrderQuery): SpySalesOrderQuery
    {
        $salesOrderQuery->addJoin(
            SpySalesOrderTableMap::COL_FK_COST_CENTER,
            SpyCostCenterTableMap::COL_ID_COST_CENTER,
            Criteria::LEFT_JOIN,
        );
        $salesOrderQuery->withColumn(SpyCostCenterTableMap::COL_NAME, static::COL_COST_CENTER_NAME);

        return $salesOrderQuery;
    }
}

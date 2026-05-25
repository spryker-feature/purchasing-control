<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableQueryExpanderPlugin;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrdersTableQueryExpanderPluginTest
 */
class CostCenterOrdersTableQueryExpanderPluginTest extends Unit
{
    public function testExpandQueryRegistersCostCenterNameVirtualColumn(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();

        // Act
        $result = (new CostCenterOrdersTableQueryExpanderPlugin())->expandQuery($query);

        // Assert
        $this->assertArrayHasKey(
            CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME,
            $result->getAsColumns(),
        );
    }

    public function testExpandQueryReturnsSameQueryInstance(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();

        // Act
        $result = (new CostCenterOrdersTableQueryExpanderPlugin())->expandQuery($query);

        // Assert
        $this->assertSame($query, $result);
    }
}

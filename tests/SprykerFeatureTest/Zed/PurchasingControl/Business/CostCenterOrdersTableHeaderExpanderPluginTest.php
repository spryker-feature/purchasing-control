<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SalesTablePluginInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableHeaderExpanderPlugin;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableQueryExpanderPlugin;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrdersTableHeaderExpanderPluginTest
 */
class CostCenterOrdersTableHeaderExpanderPluginTest extends Unit
{
    public function testExpandHeadersAddsCostCenterColumnWithCorrectLabel(): void
    {
        // Act
        $result = (new CostCenterOrdersTableHeaderExpanderPlugin())->expandHeaders([]);

        // Assert
        $this->assertArrayHasKey(CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME, $result);
        $this->assertSame('Cost Center', $result[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME]);
    }

    public function testExpandHeadersInsertsCostCenterColumnBeforeActionsColumn(): void
    {
        // Arrange
        $headers = [
            'reference' => 'Order Reference',
            SalesTablePluginInterface::ROW_ACTIONS => 'Actions',
        ];

        // Act
        $result = (new CostCenterOrdersTableHeaderExpanderPlugin())->expandHeaders($headers);

        // Assert
        $keys = array_keys($result);
        $costCenterIndex = array_search(CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME, $keys);
        $actionsIndex = array_search(SalesTablePluginInterface::ROW_ACTIONS, $keys);

        $this->assertNotFalse($costCenterIndex);
        $this->assertNotFalse($actionsIndex);
        $this->assertLessThan($actionsIndex, $costCenterIndex);
    }

    public function testExpandHeadersAddsCostCenterColumnWhenActionsColumnIsAbsent(): void
    {
        // Arrange
        $headers = ['reference' => 'Order Reference'];

        // Act
        $result = (new CostCenterOrdersTableHeaderExpanderPlugin())->expandHeaders($headers);

        // Assert
        $this->assertArrayHasKey(CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME, $result);
        $this->assertArrayNotHasKey(SalesTablePluginInterface::ROW_ACTIONS, $result);
    }
}

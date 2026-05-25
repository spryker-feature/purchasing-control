<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableQueryExpanderPlugin;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterSalesTablePlugin;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterSalesTablePluginTest
 */
class CostCenterSalesTablePluginTest extends Unit
{
    public function testFormatTableRowSetsFallbackWhenCostCenterNameIsEmpty(): void
    {
        // Arrange
        $item = [CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME => ''];

        // Act
        $result = (new CostCenterSalesTablePlugin())->formatTableRow(static fn () => '', $item);

        // Assert
        $this->assertSame('-', $result[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME]);
    }

    public function testFormatTableRowSetsFallbackWhenCostCenterNameKeyIsMissing(): void
    {
        // Act
        $result = (new CostCenterSalesTablePlugin())->formatTableRow(static fn () => '', []);

        // Assert
        $this->assertSame('-', $result[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME]);
    }

    public function testFormatTableRowPreservesCostCenterNameWhenPresent(): void
    {
        // Arrange
        $item = [CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME => 'Marketing'];

        // Act
        $result = (new CostCenterSalesTablePlugin())->formatTableRow(static fn () => '', $item);

        // Assert
        $this->assertSame('Marketing', $result[CostCenterOrdersTableQueryExpanderPlugin::COL_COST_CENTER_NAME]);
    }
}

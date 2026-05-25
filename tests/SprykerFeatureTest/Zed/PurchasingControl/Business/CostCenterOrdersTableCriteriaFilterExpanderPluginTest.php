<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTableCriteriaTransfer;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableCriteriaFilterExpanderPlugin;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrdersTableCriteriaFilterExpanderPluginTest
 */
class CostCenterOrdersTableCriteriaFilterExpanderPluginTest extends Unit
{
    public function testExpandCriteriaAppliesCostCenterFilterWhenIdsProvided(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();
        $criteriaTransfer = (new OrderTableCriteriaTransfer())->setCostCenterIds([1, 2]);

        // Act
        (new CostCenterOrdersTableCriteriaFilterExpanderPlugin())->expandCriteria($query, $criteriaTransfer);

        // Assert
        $this->assertTrue($query->containsKey(SpySalesOrderTableMap::COL_FK_COST_CENTER));
    }

    public function testExpandCriteriaAppliesBudgetFilterWhenIdsProvided(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();
        $criteriaTransfer = (new OrderTableCriteriaTransfer())->setBudgetIds([1, 2]);

        // Act
        (new CostCenterOrdersTableCriteriaFilterExpanderPlugin())->expandCriteria($query, $criteriaTransfer);

        // Assert
        $this->assertTrue($query->containsKey(SpySalesOrderTableMap::COL_FK_BUDGET));
    }

    public function testExpandCriteriaIsNoOpWhenCriteriaIsEmpty(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();
        $criteriaTransfer = new OrderTableCriteriaTransfer();

        // Act
        (new CostCenterOrdersTableCriteriaFilterExpanderPlugin())->expandCriteria($query, $criteriaTransfer);

        // Assert
        $this->assertFalse($query->containsKey(SpySalesOrderTableMap::COL_FK_COST_CENTER));
        $this->assertFalse($query->containsKey(SpySalesOrderTableMap::COL_FK_BUDGET));
    }

    public function testExpandCriteriaReturnsSameQueryInstance(): void
    {
        // Arrange
        $query = SpySalesOrderQuery::create();
        $criteriaTransfer = new OrderTableCriteriaTransfer();

        // Act
        $result = (new CostCenterOrdersTableCriteriaFilterExpanderPlugin())->expandCriteria($query, $criteriaTransfer);

        // Assert
        $this->assertSame($query, $result);
    }
}

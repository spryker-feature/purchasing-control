<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Order;

use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Generated\Shared\Transfer\QueryJoinTransfer;
use Generated\Shared\Transfer\QueryWhereConditionTransfer;

class OrderSearchQueryExpander implements OrderSearchQueryExpanderInterface
{
    protected const string FILTER_FIELD_TYPE_COST_CENTER = 'costCenter';

    protected const string FILTER_FIELD_TYPE_BUDGET = 'budget';

    /**
     * @uses \Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap::COL_FK_COST_CENTER
     */
    protected const string COL_FK_COST_CENTER = 'spy_sales_order.fk_cost_center';

    /**
     * @uses \Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap::COL_FK_BUDGET
     */
    protected const string COL_FK_BUDGET = 'spy_sales_order.fk_budget';

    /**
     * @uses \Propel\Runtime\ActiveQuery\Criteria::EQUAL
     */
    protected const string COMPARISON_EQUAL = '=';

    /**
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     */
    public function isApplicable(array $filterFieldTransfers): bool
    {
        foreach ($filterFieldTransfers as $filterFieldTransfer) {
            $type = $filterFieldTransfer->getType();

            if ($type === static::FILTER_FIELD_TYPE_COST_CENTER || $type === static::FILTER_FIELD_TYPE_BUDGET) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     */
    public function expandQueryJoinCollection(
        array $filterFieldTransfers,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer,
    ): QueryJoinCollectionTransfer {
        foreach ($filterFieldTransfers as $filterFieldTransfer) {
            $type = $filterFieldTransfer->getType();

            if ($type === static::FILTER_FIELD_TYPE_COST_CENTER) {
                $queryJoinCollectionTransfer->addQueryJoin(
                    $this->createWhereConditionQueryJoin(
                        static::COL_FK_COST_CENTER,
                        $filterFieldTransfer->getValueOrFail(),
                    ),
                );
            }

            if ($type === static::FILTER_FIELD_TYPE_BUDGET) {
                $queryJoinCollectionTransfer->addQueryJoin(
                    $this->createWhereConditionQueryJoin(
                        static::COL_FK_BUDGET,
                        $filterFieldTransfer->getValueOrFail(),
                    ),
                );
            }
        }

        return $queryJoinCollectionTransfer;
    }

    protected function createWhereConditionQueryJoin(string $column, string $value): QueryJoinTransfer
    {
        $queryWhereConditionTransfer = (new QueryWhereConditionTransfer())
            ->setColumn($column)
            ->setValue($value)
            ->setComparison(static::COMPARISON_EQUAL);

        return (new QueryJoinTransfer())->addQueryWhereCondition($queryWhereConditionTransfer);
    }
}

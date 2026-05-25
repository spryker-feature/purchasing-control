<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\Handler;

use Generated\Shared\Transfer\FilterFieldTransfer;
use Generated\Shared\Transfer\OrderListTransfer;

class CostCenterOrderSearchFormHandler implements CostCenterOrderSearchFormHandlerInterface
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Form\OrderSearchForm::FIELD_FILTERS
     */
    protected const string FIELD_FILTERS = 'filters';

    protected const string FIELD_COST_CENTER_IDS = 'costCenterIds';

    protected const string FIELD_BUDGET_IDS = 'budgetIds';

    protected const string FILTER_FIELD_TYPE_COST_CENTER = 'costCenter';

    protected const string FILTER_FIELD_TYPE_BUDGET = 'budget';

    /**
     * @param array<string, mixed> $orderSearchFormData
     */
    public function handle(array $orderSearchFormData, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        $filters = $orderSearchFormData[static::FIELD_FILTERS] ?? [];

        $orderListTransfer = $this->addCostCenterFilterFields($filters, $orderListTransfer);
        $orderListTransfer = $this->addBudgetFilterFields($filters, $orderListTransfer);

        return $orderListTransfer;
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function addCostCenterFilterFields(array $filters, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        $costCenterIds = array_filter((array)($filters[static::FIELD_COST_CENTER_IDS] ?? []));

        foreach ($costCenterIds as $idCostCenter) {
            $orderListTransfer->addFilterField(
                (new FilterFieldTransfer())
                    ->setType(static::FILTER_FIELD_TYPE_COST_CENTER)
                    ->setValue((string)$idCostCenter),
            );
        }

        return $orderListTransfer;
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function addBudgetFilterFields(array $filters, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        $budgetIds = array_filter((array)($filters[static::FIELD_BUDGET_IDS] ?? []));

        foreach ($budgetIds as $idBudget) {
            $orderListTransfer->addFilterField(
                (new FilterFieldTransfer())
                    ->setType(static::FILTER_FIELD_TYPE_BUDGET)
                    ->setValue((string)$idBudget),
            );
        }

        return $orderListTransfer;
    }
}

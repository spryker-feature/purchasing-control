<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl\Zed;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class PurchasingControlStub implements PurchasingControlStubInterface
{
    public function __construct(protected readonly ZedRequestClientInterface $zedRequestClient)
    {
    }

    public function getActiveCostCentersForCompanyBusinessUnit(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        /** @var \Generated\Shared\Transfer\CostCenterCollectionTransfer $costCenterCollectionTransfer */
        $costCenterCollectionTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/get-active-cost-centers-for-company-business-unit',
            $costCenterCriteriaTransfer,
        );

        return $costCenterCollectionTransfer;
    }

    public function getActiveBudgetsForCostCenter(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        /** @var \Generated\Shared\Transfer\BudgetCollectionTransfer $budgetCollectionTransfer */
        $budgetCollectionTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/get-active-budgets-for-cost-center',
            $budgetCriteriaTransfer,
        );

        return $budgetCollectionTransfer;
    }
}

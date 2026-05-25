<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl\Zed;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class PurchasingControlStub implements PurchasingControlStubInterface
{
    public function __construct(protected readonly ZedRequestClientInterface $zedRequestClient)
    {
    }

    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        /** @var \Generated\Shared\Transfer\CostCenterCollectionTransfer $costCenterCollectionTransfer */
        $costCenterCollectionTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/get-cost-center-collection',
            $costCenterCriteriaTransfer,
        );

        return $costCenterCollectionTransfer;
    }

    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        /** @var \Generated\Shared\Transfer\BudgetCollectionTransfer $budgetCollectionTransfer */
        $budgetCollectionTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/get-budget-collection',
            $budgetCriteriaTransfer,
        );

        return $budgetCollectionTransfer;
    }

    public function createCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\CostCenterCollectionResponseTransfer $costCenterCollectionResponseTransfer */
        $costCenterCollectionResponseTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/create-cost-center-collection',
            $costCenterCollectionRequestTransfer,
        );

        return $costCenterCollectionResponseTransfer;
    }

    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\CostCenterCollectionResponseTransfer $costCenterCollectionResponseTransfer */
        $costCenterCollectionResponseTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/update-cost-center-collection',
            $costCenterCollectionRequestTransfer,
        );

        return $costCenterCollectionResponseTransfer;
    }

    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer $responseTransfer */
        $responseTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/update-quote-cost-center',
            $requestTransfer,
        );

        return $responseTransfer;
    }

    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\BudgetCollectionResponseTransfer $budgetCollectionResponseTransfer */
        $budgetCollectionResponseTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/create-budget-collection',
            $budgetCollectionRequestTransfer,
        );

        return $budgetCollectionResponseTransfer;
    }

    public function updateBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\BudgetCollectionResponseTransfer $budgetCollectionResponseTransfer */
        $budgetCollectionResponseTransfer = $this->zedRequestClient->call(
            '/purchasing-control/gateway/update-budget-collection',
            $budgetCollectionRequestTransfer,
        );

        return $budgetCollectionResponseTransfer;
    }
}

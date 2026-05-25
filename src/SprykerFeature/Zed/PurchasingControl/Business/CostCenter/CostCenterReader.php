<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterReader implements CostCenterReaderInterface
{
    public function __construct(
        protected PurchasingControlRepositoryInterface $costCenterRepository,
        protected CostCenterBudgetExpanderInterface $costCenterBudgetExpander,
    ) {
    }

    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        $costCenterCollectionTransfer = $this->costCenterRepository->getCostCenterCollection($costCenterCriteriaTransfer);

        if (!$costCenterCriteriaTransfer->getCostCenterConditions()?->getWithBudgets()) {
            return $costCenterCollectionTransfer;
        }

        return $this->costCenterBudgetExpander->expandWithBudgets($costCenterCollectionTransfer, $costCenterCriteriaTransfer);
    }
}

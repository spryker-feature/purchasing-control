<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;

interface CostCenterBudgetExpanderInterface
{
    public function expandWithBudgets(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        CostCenterCriteriaTransfer $costCenterCriteriaTransfer,
    ): CostCenterCollectionTransfer;
}

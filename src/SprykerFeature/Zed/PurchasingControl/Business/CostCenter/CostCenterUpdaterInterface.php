<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;

interface CostCenterUpdaterInterface
{
    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;
}

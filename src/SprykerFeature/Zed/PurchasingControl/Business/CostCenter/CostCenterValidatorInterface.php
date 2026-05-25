<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\ErrorTransfer;

interface CostCenterValidatorInterface
{
    public function validate(CostCenterTransfer $costCenterTransfer, ?int $idCompany = null): ?ErrorTransfer;

    /**
     * @return array<int, true>
     */
    public function validateCostCenterCollection(
        CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer,
        CostCenterCollectionResponseTransfer $costCenterCollectionResponseTransfer,
    ): array;
}

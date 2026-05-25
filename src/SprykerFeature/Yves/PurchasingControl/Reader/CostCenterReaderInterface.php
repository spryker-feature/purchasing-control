<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;

interface CostCenterReaderInterface
{
    public function getActiveCostCentersForCompanyBusinessUnit(
        int $idCompanyBusinessUnit,
        string $currencyIsoCode,
        bool $isLocked = false,
    ): CostCenterCollectionTransfer;

    public function findCostCenter(string $costCenterUuid, int $idCompany): ?CostCenterTransfer;

    public function getCostCentersWithBudgetsForOrderSearch(?int $idCompanyBusinessUnit, ?int $idCompany = null): CostCenterCollectionTransfer;
}

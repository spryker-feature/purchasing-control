<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterReader implements CostCenterReaderInterface
{
    public function __construct(protected PurchasingControlRepositoryInterface $costCenterRepository)
    {
    }

    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        return $this->costCenterRepository->findCostCenterCollection($costCenterCriteriaTransfer);
    }

    public function getCostCenterById(int $idCostCenter): CostCenterTransfer
    {
        $costCenterTransfer = $this->costCenterRepository->findCostCenterById($idCostCenter);

        if ($costCenterTransfer === null) {
            return new CostCenterTransfer();
        }

        return $costCenterTransfer;
    }
}

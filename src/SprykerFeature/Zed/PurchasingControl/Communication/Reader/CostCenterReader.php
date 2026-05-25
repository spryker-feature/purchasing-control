<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Reader;

use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface;

class CostCenterReader implements CostCenterReaderInterface
{
    public function __construct(protected PurchasingControlFacadeInterface $purchasingControlFacade)
    {
    }

    public function findCostCenterName(int $idCostCenter): string
    {
        $costCenterCollectionTransfer = $this->purchasingControlFacade->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCostCenter($idCostCenter),
            ),
        );

        $costCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current() ?: null;

        return $costCenterTransfer?->getName() ?? sprintf('#%d', $idCostCenter);
    }
}

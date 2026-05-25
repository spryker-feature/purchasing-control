<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Resolver;

use ArrayObject;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface CostCenterResolverInterface
{
    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer>
     */
    public function resolveCostCenters(QuoteTransfer $quoteTransfer): ArrayObject;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenterTransfers
     */
    public function resolveSelectedCostCenter(ArrayObject $costCenterTransfers, ?int $idCostCenter): ?CostCenterTransfer;
}

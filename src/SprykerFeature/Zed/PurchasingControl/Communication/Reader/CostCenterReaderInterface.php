<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Reader;

interface CostCenterReaderInterface
{
    /**
     * @api
     */
    public function findCostCenterName(int $idCostCenter): string;
}

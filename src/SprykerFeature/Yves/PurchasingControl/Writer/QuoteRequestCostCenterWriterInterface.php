<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Writer;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;

interface QuoteRequestCostCenterWriterInterface
{
    public function updateCostCenter(
        QuoteRequestTransfer $quoteRequestTransfer,
        ?int $idCostCenter,
        ?int $idBudget
    ): QuoteRequestResponseTransfer;
}

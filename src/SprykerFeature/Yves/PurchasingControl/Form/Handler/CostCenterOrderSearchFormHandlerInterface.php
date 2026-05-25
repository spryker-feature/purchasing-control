<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\Handler;

use Generated\Shared\Transfer\OrderListTransfer;

interface CostCenterOrderSearchFormHandlerInterface
{
    /**
     * @param array<string, mixed> $orderSearchFormData
     */
    public function handle(array $orderSearchFormData, OrderListTransfer $orderListTransfer): OrderListTransfer;
}

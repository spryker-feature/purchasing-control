<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Order;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class CostCenterOrderSaver implements CostCenterOrderSaverInterface
{
    public function __construct(protected PurchasingControlEntityManagerInterface $costCenterEntityManager)
    {
    }

    public function saveCostCenterToOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        if ($quoteTransfer->getIdCostCenter() === null) {
            return;
        }

        $this->costCenterEntityManager->updateSalesOrderCostCenter(
            $saveOrderTransfer->getIdSalesOrderOrFail(),
            $quoteTransfer->getIdCostCenter(),
            $quoteTransfer->getIdBudget(),
        );
    }
}

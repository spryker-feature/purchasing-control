<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class BudgetConsumer implements BudgetConsumerInterface
{
    public function __construct(
        protected readonly PurchasingControlEntityManagerInterface $purchasingControlEntityManager,
    ) {
    }

    public function consumeBudgetFromQuote(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): void
    {
        $idBudget = $quoteTransfer->getIdBudget();
        if ($idBudget === null) {
            return;
        }

        $grandTotal = 0;
        if ($quoteTransfer->getTotals() !== null) {
            $grandTotal = $quoteTransfer->getTotalsOrFail()->getGrandTotal() ?? 0;
        }

        $idSalesOrder = $checkoutResponseTransfer->getSaveOrderOrFail()->getIdSalesOrderOrFail();
        $budgetConsumptionTransfer = (new BudgetConsumptionTransfer())
            ->setIdBudget($idBudget)
            ->setIdSalesOrder($idSalesOrder)
            ->setAmount($grandTotal);

        $this->purchasingControlEntityManager->createBudgetConsumption($budgetConsumptionTransfer);
    }
}

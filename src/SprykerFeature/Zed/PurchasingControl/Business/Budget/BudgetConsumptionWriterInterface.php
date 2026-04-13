<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

interface BudgetConsumptionWriterInterface
{
    /**
     * Specification:
     * - Creates a budget consumption record linking the given budget to the sales order.
     * - Records the consumed amount in cents.
     */
    public function consumeBudget(int $idBudget, int $idSalesOrder, int $amountInCents): void;

    /**
     * Specification:
     * - Deletes all budget consumption records for the given sales order.
     * - Restores the consumed budget balance by removing the consumption records.
     */
    public function restoreBudget(int $idSalesOrder): void;
}

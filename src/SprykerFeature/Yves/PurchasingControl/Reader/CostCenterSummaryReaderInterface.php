<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

interface CostCenterSummaryReaderInterface
{
    public function getActiveCostCenterCount(): int;

    /**
     * @return array<string, array{totalAmount: int, totalConsumedAmount: int, totalRemainingAmount: int}>
     */
    public function getBudgetSummaries(): array;

    /**
     * @return array<int, array{name: string, budgets: array<int, array{name: string, amount: int, consumedAmount: int, remainingAmount: int, currency: string}>}>
     */
    public function getCostCenterBudgetDetails(): array;
}

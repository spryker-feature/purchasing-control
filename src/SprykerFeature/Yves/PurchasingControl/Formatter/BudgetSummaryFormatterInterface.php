<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Formatter;

use Generated\Shared\Transfer\BudgetSummaryTransfer;
use Generated\Shared\Transfer\BudgetTransfer;

interface BudgetSummaryFormatterInterface
{
    public function formatBudgetSummary(BudgetTransfer $budgetTransfer, string $currencyIsoCode): BudgetSummaryTransfer;
}

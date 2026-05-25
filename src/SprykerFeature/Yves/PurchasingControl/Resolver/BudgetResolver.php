<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Resolver;

use ArrayObject;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class BudgetResolver implements BudgetResolverInterface
{
    public function resolveSelectedBudget(ArrayObject $budgetTransfers, QuoteTransfer $quoteTransfer): ?BudgetTransfer
    {
        $idBudget = $quoteTransfer->getIdBudget();
        $isLocked = $quoteTransfer->getIsLocked() === true;

        if ($idBudget !== null) {
            foreach ($budgetTransfers as $budgetTransfer) {
                if ($budgetTransfer->getIdBudget() === $idBudget) {
                    return $budgetTransfer;
                }
            }

            if (!$isLocked) {
                return null;
            }
        }

        if ($budgetTransfers->count() === 1) {
            return $budgetTransfers[0];
        }

        return null;
    }
}

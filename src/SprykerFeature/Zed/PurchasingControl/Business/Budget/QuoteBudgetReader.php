<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class QuoteBudgetReader implements QuoteBudgetReaderInterface
{
    public function __construct(protected readonly PurchasingControlRepositoryInterface $purchasingControlRepository)
    {
    }

    public function findBudgetForQuote(QuoteTransfer $quoteTransfer, bool $withBudgetConsumption = false): ?BudgetTransfer
    {
        if ($quoteTransfer->getIdBudget() === null) {
            return null;
        }

        $budgetCollectionTransfer = $this->purchasingControlRepository->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addIdBudget($quoteTransfer->getIdBudgetOrFail())
                    ->setWithBudgetConsumption($withBudgetConsumption),
            ),
        );

        return $budgetCollectionTransfer->getBudgets()->getIterator()->current() ?: null;
    }
}

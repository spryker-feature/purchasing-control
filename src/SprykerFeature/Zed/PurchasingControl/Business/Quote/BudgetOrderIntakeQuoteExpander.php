<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\OrderIntakeRequestTransfer;
use Generated\Shared\Transfer\OrderIntakeResponseTransfer;
use Generated\Shared\Transfer\OrderIntakeValidationIssueTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;

class BudgetOrderIntakeQuoteExpander implements BudgetOrderIntakeQuoteExpanderInterface
{
    protected const string FIELD_BUDGET_UUID = 'budgetUuid';

    protected const string ERROR_BUDGET_UNKNOWN = 'Budget "%s" was not found.';

    public function __construct(
        protected readonly BudgetReaderInterface $budgetReader,
    ) {
    }

    public function expand(
        OrderIntakeRequestTransfer $orderIntakeRequestTransfer,
        QuoteTransfer $quoteTransfer,
        OrderIntakeResponseTransfer $orderIntakeResponseTransfer,
    ): QuoteTransfer {
        $budgetUuid = $orderIntakeRequestTransfer->getBudgetUuid();

        if ($budgetUuid === null || $budgetUuid === '') {
            return $quoteTransfer;
        }

        $budgetTransfer = $this->budgetReader->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())->addUuid($budgetUuid),
            ),
        )->getBudgets()->getIterator()->current() ?: null;

        if ($budgetTransfer === null) {
            $orderIntakeResponseTransfer->addValidationIssue(
                (new OrderIntakeValidationIssueTransfer())
                    ->setField(static::FIELD_BUDGET_UUID)
                    ->setMessage(sprintf(static::ERROR_BUDGET_UNKNOWN, $budgetUuid)),
            );

            return $quoteTransfer;
        }

        return $quoteTransfer
            ->setIdBudget($budgetTransfer->getIdBudget())
            ->setIdCostCenter($budgetTransfer->getIdCostCenter());
    }
}

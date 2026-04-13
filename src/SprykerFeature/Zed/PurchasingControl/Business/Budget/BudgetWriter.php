<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetResponseTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class BudgetWriter implements BudgetWriterInterface
{
    protected const ERROR_DATE_RANGE_INVALID = 'Budget start date must be before end date.';

    protected const ERROR_AMOUNT_INVALID = 'Budget amount must be greater than zero.';

    protected const ERROR_CURRENCY_INVALID = 'Budget currency must be a 3-character ISO code.';

    public function __construct(protected PurchasingControlEntityManagerInterface $costCenterEntityManager)
    {
    }

    public function createBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer
    {
        $budgetResponseTransfer = $this->validate($budgetTransfer);

        if (!$budgetResponseTransfer->getIsSuccessful()) {
            return $budgetResponseTransfer;
        }

        $budgetTransfer = $this->costCenterEntityManager->createBudget($budgetTransfer);

        return $budgetResponseTransfer
            ->setIsSuccessful(true)
            ->setBudget($budgetTransfer);
    }

    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer
    {
        $budgetResponseTransfer = $this->validate($budgetTransfer);

        if (!$budgetResponseTransfer->getIsSuccessful()) {
            return $budgetResponseTransfer;
        }

        $budgetTransfer = $this->costCenterEntityManager->updateBudget($budgetTransfer);

        return $budgetResponseTransfer
            ->setIsSuccessful(true)
            ->setBudget($budgetTransfer);
    }

    protected function validate(BudgetTransfer $budgetTransfer): BudgetResponseTransfer
    {
        $budgetResponseTransfer = (new BudgetResponseTransfer())->setIsSuccessful(true);

        if ($budgetTransfer->getAmount() === null || $budgetTransfer->getAmount() <= 0) {
            $budgetResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new MessageTransfer())->setValue(static::ERROR_AMOUNT_INVALID));
        }

        if ($budgetTransfer->getCurrencyIsoCode() === null || strlen($budgetTransfer->getCurrencyIsoCode()) !== 3) {
            $budgetResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new MessageTransfer())->setValue(static::ERROR_CURRENCY_INVALID));
        }

        if ($budgetTransfer->getStartsAt() !== null && $budgetTransfer->getEndsAt() !== null) {
            if ($budgetTransfer->getStartsAt() >= $budgetTransfer->getEndsAt()) {
                $budgetResponseTransfer
                    ->setIsSuccessful(false)
                    ->addError((new MessageTransfer())->setValue(static::ERROR_DATE_RANGE_INVALID));
            }
        }

        return $budgetResponseTransfer;
    }
}

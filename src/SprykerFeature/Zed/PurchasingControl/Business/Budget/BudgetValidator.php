<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class BudgetValidator implements BudgetValidatorInterface
{
    protected const string GLOSSARY_KEY_VALIDATION_ACCESS_DENIED = 'purchasing_control.budget.validation.access_denied';

    protected const string GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID = 'purchasing_control.budget.validation.amount_invalid';

    protected const string GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID = 'purchasing_control.budget.validation.date_range_invalid';

    protected const string GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID = 'purchasing_control.budget.validation.currency_invalid';

    protected const string GLOSSARY_KEY_VALIDATION_CURRENCY_CHANGED = 'purchasing_control.budget.validation.currency_changed';

    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $purchasingControlRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validateBudget(BudgetTransfer $budgetTransfer, ?int $idCompany = null): array
    {
        $errors = [];
        $entityIdentifier = (string)$budgetTransfer->getIdBudget();

        if ($idCompany !== null && $this->isExistingBudgetAccessDenied($budgetTransfer, $idCompany)) {
            return [(new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_ACCESS_DENIED)
                ->setEntityIdentifier($entityIdentifier)];
        }

        if ($budgetTransfer->getAmount() === null || $budgetTransfer->getAmount() <= 0) {
            $errors[] = (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID)
                ->setEntityIdentifier($entityIdentifier);
        }

        if ($budgetTransfer->getCurrencyIsoCode() === null || strlen($budgetTransfer->getCurrencyIsoCode()) !== 3) {
            $errors[] = (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID)
                ->setEntityIdentifier($entityIdentifier);
        }

        if ($this->isExistingBudgetCurrencyChanged($budgetTransfer)) {
            $errors[] = (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_CURRENCY_CHANGED)
                ->setEntityIdentifier($entityIdentifier);
        }

        if (
            $budgetTransfer->getStartsAt() !== null && $budgetTransfer->getEndsAt() !== null
            && $budgetTransfer->getStartsAt() >= $budgetTransfer->getEndsAt()
        ) {
            $errors[] = (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID)
                ->setEntityIdentifier($entityIdentifier);
        }

        return $errors;
    }

    /**
     * {@inheritDoc}
     */
    public function validateBudgetCollection(
        BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer,
        BudgetCollectionResponseTransfer $responseTransfer,
    ): array {
        $idCompany = $budgetCollectionRequestTransfer->getCustomer()?->getCompanyUserTransfer()?->getFkCompany();
        $invalidIndices = [];

        foreach ($budgetCollectionRequestTransfer->getBudgets() as $index => $budgetTransfer) {
            $errorTransfers = $this->validateBudget($budgetTransfer, $idCompany);

            if ($errorTransfers === []) {
                continue;
            }

            $invalidIndices[(int)$index] = true;

            foreach ($errorTransfers as $errorTransfer) {
                $responseTransfer->addError($errorTransfer);
            }
        }

        return $invalidIndices;
    }

    protected function isExistingBudgetAccessDenied(BudgetTransfer $budgetTransfer, int $idCompany): bool
    {
        if ($budgetTransfer->getIdCostCenter() === null) {
            return false;
        }

        $costCenterCollectionTransfer = $this->purchasingControlRepository->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addIdCostCenter($budgetTransfer->getIdCostCenterOrFail())
                    ->addIdCompany($idCompany),
            ),
        );

        return $costCenterCollectionTransfer->getCostCenters()->count() === 0;
    }

    protected function isExistingBudgetCurrencyChanged(BudgetTransfer $budgetTransfer): bool
    {
        if ($budgetTransfer->getIdBudget() === null) {
            return false;
        }

        $budgetCollectionTransfer = $this->purchasingControlRepository->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdBudget($budgetTransfer->getIdBudgetOrFail()),
            ),
        );
        $existingBudgetTransfer = $budgetCollectionTransfer->getBudgets()->getIterator()->current() ?: null;

        if ($existingBudgetTransfer === null) {
            return false;
        }

        return $existingBudgetTransfer->getCurrencyIsoCode() !== $budgetTransfer->getCurrencyIsoCode();
    }
}

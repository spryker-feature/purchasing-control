<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use ArrayObject;
use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use SprykerFeature\Zed\PurchasingControl\Business\CollectionIndexOperations;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class BudgetCreator implements BudgetCreatorInterface
{
    use TransactionTrait;

    public function __construct(
        protected PurchasingControlEntityManagerInterface $entityManager,
        protected BudgetValidatorInterface $budgetValidator,
        protected CollectionIndexOperations $collectionIndexOperations,
    ) {
    }

    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer
    {
        $budgetCollectionResponseTransfer = (new BudgetCollectionResponseTransfer())
            ->setBudgets($budgetCollectionRequestTransfer->getBudgets());

        $invalidIndices = $this->budgetValidator->validateBudgetCollection(
            $budgetCollectionRequestTransfer,
            $budgetCollectionResponseTransfer,
        );

        if ($budgetCollectionRequestTransfer->getIsTransactional() && $budgetCollectionResponseTransfer->getErrors()->count()) {
            return $budgetCollectionResponseTransfer;
        }

        [$validBudgetTransfers, $invalidBudgetTransfers] = $this->collectionIndexOperations->splitByInvalidIndices(
            $budgetCollectionRequestTransfer->getBudgets(),
            $invalidIndices,
        );

        if ($validBudgetTransfers->count()) {
            $validBudgetTransfers = $this->getTransactionHandler()->handleTransaction(
                fn () => $this->executeCreateBudgetCollectionTransaction($validBudgetTransfers),
            );
        }

        return $budgetCollectionResponseTransfer->setBudgets($this->collectionIndexOperations->mergeItems($validBudgetTransfers, $invalidBudgetTransfers));
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer>
     */
    protected function executeCreateBudgetCollectionTransaction(ArrayObject $budgetTransfers): ArrayObject
    {
        $persistedTransfers = new ArrayObject();

        foreach ($budgetTransfers as $index => $budgetTransfer) {
            $persistedTransfers->offsetSet($index, $this->entityManager->createBudget($budgetTransfer));
        }

        return $persistedTransfers;
    }
}

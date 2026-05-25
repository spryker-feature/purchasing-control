<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use ArrayObject;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use SprykerFeature\Zed\PurchasingControl\Business\CollectionIndexOperations;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class CostCenterUpdater implements CostCenterUpdaterInterface
{
    use TransactionTrait;

    public function __construct(
        protected PurchasingControlEntityManagerInterface $entityManager,
        protected CostCenterValidatorInterface $costCenterValidator,
        protected CollectionIndexOperations $collectionIndexOperations,
    ) {
    }

    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer
    {
        $costCenterCollectionResponseTransfer = (new CostCenterCollectionResponseTransfer())
            ->setCostCenters($costCenterCollectionRequestTransfer->getCostCenters());

        $invalidIndices = $this->costCenterValidator->validateCostCenterCollection(
            $costCenterCollectionRequestTransfer,
            $costCenterCollectionResponseTransfer,
        );

        if ($costCenterCollectionRequestTransfer->getIsTransactional() && $costCenterCollectionResponseTransfer->getErrors()->count()) {
            return $costCenterCollectionResponseTransfer;
        }

        [$validCostCenterTransfers, $invalidCostCenterTransfers] = $this->collectionIndexOperations->splitByInvalidIndices(
            $costCenterCollectionRequestTransfer->getCostCenters(),
            $invalidIndices,
        );

        if ($validCostCenterTransfers->count()) {
            $validCostCenterTransfers = $this->getTransactionHandler()->handleTransaction(
                fn () => $this->executeUpdateCostCenterCollectionTransaction($validCostCenterTransfers),
            );
        }

        return $costCenterCollectionResponseTransfer->setCostCenters($this->collectionIndexOperations->mergeItems($validCostCenterTransfers, $invalidCostCenterTransfers));
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenterTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer>
     */
    protected function executeUpdateCostCenterCollectionTransaction(ArrayObject $costCenterTransfers): ArrayObject
    {
        $persistedTransfers = new ArrayObject();

        foreach ($costCenterTransfers as $index => $costCenterTransfer) {
            $persistedTransfer = $this->entityManager->updateCostCenter($costCenterTransfer);

            if ($persistedTransfer->getIsActive() === false) {
                $this->entityManager->deactivateBudgetsByCostCenterId($persistedTransfer->getIdCostCenterOrFail());
            }

            $persistedTransfers->offsetSet($index, $persistedTransfer);
        }

        return $persistedTransfers;
    }
}

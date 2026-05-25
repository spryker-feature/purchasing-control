<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Order;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;

class CostCenterOrderListExpander implements CostCenterOrderListExpanderInterface
{
    public function __construct(
        protected readonly CostCenterReaderInterface $costCenterReader,
        protected readonly BudgetReaderInterface $budgetReader,
    ) {
    }

    /**
     * @param array<\Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     *
     * @return array<\Generated\Shared\Transfer\OrderTransfer>
     */
    public function expandOrders(array $orderTransfers): array
    {
        $costCenterIds = $this->extractUniqueCostCenterIds($orderTransfers);
        $budgetIds = $this->extractUniqueBudgetIds($orderTransfers);

        $indexedCostCenters = $this->fetchCostCentersIndexedById($costCenterIds);
        $indexedBudgets = $this->fetchBudgetsIndexedById($budgetIds);

        foreach ($orderTransfers as $orderTransfer) {
            $idCostCenter = $orderTransfer->getFkCostCenter();

            if ($idCostCenter !== null && isset($indexedCostCenters[$idCostCenter])) {
                $orderTransfer->setCostCenter($indexedCostCenters[$idCostCenter]);
            }

            $idBudget = $orderTransfer->getFkBudget();

            if ($idBudget !== null && isset($indexedBudgets[$idBudget])) {
                $orderTransfer->setBudget($indexedBudgets[$idBudget]);
            }
        }

        return $orderTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     *
     * @return array<int>
     */
    protected function extractUniqueCostCenterIds(array $orderTransfers): array
    {
        $ids = [];

        foreach ($orderTransfers as $orderTransfer) {
            $idCostCenter = $orderTransfer->getFkCostCenter();

            if ($idCostCenter !== null) {
                $ids[$idCostCenter] = $idCostCenter;
            }
        }

        return array_values($ids);
    }

    /**
     * @param array<\Generated\Shared\Transfer\OrderTransfer> $orderTransfers
     *
     * @return array<int>
     */
    protected function extractUniqueBudgetIds(array $orderTransfers): array
    {
        $ids = [];

        foreach ($orderTransfers as $orderTransfer) {
            $idBudget = $orderTransfer->getFkBudget();

            if ($idBudget !== null) {
                $ids[$idBudget] = $idBudget;
            }
        }

        return array_values($ids);
    }

    /**
     * @param array<int> $costCenterIds
     *
     * @return array<int, \Generated\Shared\Transfer\CostCenterTransfer>
     */
    protected function fetchCostCentersIndexedById(array $costCenterIds): array
    {
        if (!$costCenterIds) {
            return [];
        }

        $costCenterCollectionTransfer = $this->costCenterReader->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())
                ->setCostCenterConditions(
                    (new CostCenterConditionsTransfer())
                        ->setCostCenterIds($costCenterIds)
                        ->setWithBudgets(false),
                ),
        );

        $indexed = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $indexed[$costCenterTransfer->getIdCostCenterOrFail()] = $costCenterTransfer;
        }

        return $indexed;
    }

    /**
     * @param array<int> $budgetIds
     *
     * @return array<int, \Generated\Shared\Transfer\BudgetTransfer>
     */
    protected function fetchBudgetsIndexedById(array $budgetIds): array
    {
        if (!$budgetIds) {
            return [];
        }

        $budgetCollectionTransfer = $this->budgetReader->getBudgetCollection(
            (new BudgetCriteriaTransfer())
                ->setBudgetConditions(
                    (new BudgetConditionsTransfer())
                        ->setBudgetIds($budgetIds)
                        ->setWithBudgetConsumption(false),
                ),
        );

        $indexed = [];

        foreach ($budgetCollectionTransfer->getBudgets() as $budgetTransfer) {
            $indexed[$budgetTransfer->getIdBudgetOrFail()] = $budgetTransfer;
        }

        return $indexed;
    }
}

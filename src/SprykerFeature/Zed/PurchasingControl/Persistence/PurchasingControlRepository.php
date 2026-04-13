<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlPersistenceFactory getFactory()
 */
class PurchasingControlRepository extends AbstractRepository implements PurchasingControlRepositoryInterface
{
    public function findCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        $costCenterCollectionTransfer = new CostCenterCollectionTransfer();
        $query = $this->getFactory()->createCostCenterQuery()
            ->leftJoinWithSpyCostCenterToCompanyBusinessUnit()
            ->leftJoinWith('SpyCostCenterToCompanyBusinessUnit.SpyCompanyBusinessUnit');

        $companyBusinessUnitIds = $costCenterCriteriaTransfer->getCompanyBusinessUnitIds();
        if ($companyBusinessUnitIds !== []) {
            $query->useSpyCostCenterToCompanyBusinessUnitQuery()
                ->filterByFkCompanyBusinessUnit_In($companyBusinessUnitIds)
                ->endUse();
        }

        if ($costCenterCriteriaTransfer->getIsActive() !== null) {
            $query->filterByIsActive($costCenterCriteriaTransfer->getIsActive());
        }

        if ($costCenterCriteriaTransfer->getCurrencyIsoCode() !== null) {
            $today = date('Y-m-d');
            $query->useSpyBudgetQuery('', Criteria::INNER_JOIN)
                ->filterByCurrencyIsoCode($costCenterCriteriaTransfer->getCurrencyIsoCode())
                ->filterByIsActive(true)
                ->filterByStartsAt($today, Criteria::LESS_EQUAL)
                ->filterByEndsAt($today, Criteria::GREATER_EQUAL)
                ->endUse()
                ->distinct();
        }

        if ($costCenterCriteriaTransfer->getPagination() !== null) {
            $query = $this->applyPagination($query, $costCenterCriteriaTransfer->getPagination());
        }

        $mapper = $this->getFactory()->createCostCenterMapper();

        foreach ($query->find() as $costCenterEntity) {
            $costCenterCollectionTransfer->addCostCenter(
                $mapper->mapCostCenterEntityToTransfer($costCenterEntity, new CostCenterTransfer()),
            );
        }

        return $costCenterCollectionTransfer;
    }

    public function findCostCenterById(int $idCostCenter): ?CostCenterTransfer
    {
        $costCenterEntity = $this->getFactory()->createCostCenterQuery()
            ->leftJoinWithSpyCostCenterToCompanyBusinessUnit()
            ->leftJoinWith('SpyCostCenterToCompanyBusinessUnit.SpyCompanyBusinessUnit')
            ->findPk($idCostCenter);

        if ($costCenterEntity === null) {
            return null;
        }

        return $this->getFactory()->createCostCenterMapper()
            ->mapCostCenterEntityToTransfer($costCenterEntity, new CostCenterTransfer());
    }

    public function findBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        $budgetCollectionTransfer = new BudgetCollectionTransfer();
        $query = $this->getFactory()->createBudgetQuery();

        $query->withColumn('COALESCE(SUM(spy_budget_consumption.amount), 0)', 'ConsumedAmount')
            ->leftJoinSpyBudgetConsumption()
            ->groupByIdBudget();

        if ($budgetCriteriaTransfer->getIdCostCenter() !== null) {
            $query->filterByFkCostCenter($budgetCriteriaTransfer->getIdCostCenter());
        }

        if ($budgetCriteriaTransfer->getIsActive() !== null) {
            $query->filterByIsActive($budgetCriteriaTransfer->getIsActive());
        }

        if ($budgetCriteriaTransfer->getCurrencyIsoCode() !== null) {
            $query->filterByCurrencyIsoCode($budgetCriteriaTransfer->getCurrencyIsoCode());
        }

        if ($budgetCriteriaTransfer->getActiveOnDate() !== null) {
            $query->filterByStartsAt($budgetCriteriaTransfer->getActiveOnDate(), Criteria::LESS_EQUAL)
                ->filterByEndsAt($budgetCriteriaTransfer->getActiveOnDate(), Criteria::GREATER_EQUAL);
        }

        $mapper = $this->getFactory()->createCostCenterMapper();

        foreach ($query->find() as $budgetEntity) {
            $budgetCollectionTransfer->addBudget(
                $mapper->mapBudgetEntityToTransfer($budgetEntity, new BudgetTransfer()),
            );
        }

        return $budgetCollectionTransfer;
    }

    public function findBudgetById(int $idBudget): ?BudgetTransfer
    {
        $budgetEntity = $this->getFactory()->createBudgetQuery()
            ->withColumn('COALESCE(SUM(spy_budget_consumption.amount), 0)', 'ConsumedAmount')
            ->leftJoinSpyBudgetConsumption()
            ->groupByIdBudget()
            ->findPk($idBudget);

        if ($budgetEntity === null) {
            return null;
        }

        return $this->getFactory()->createCostCenterMapper()
            ->mapBudgetEntityToTransfer($budgetEntity, new BudgetTransfer());
    }

    protected function applyPagination(SpyCostCenterQuery $query, PaginationTransfer $paginationTransfer): SpyCostCenterQuery
    {
        if ($paginationTransfer->getLimit() !== null) {
            $query->limit($paginationTransfer->getLimit());
        }

        if ($paginationTransfer->getOffset() !== null) {
            $query->offset($paginationTransfer->getOffset());
        }

        return $query;
    }
}

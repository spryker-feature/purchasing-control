<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use ArrayObject;
use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetConsumptionCollectionTransfer;
use Generated\Shared\Transfer\BudgetConsumptionCriteriaTransfer;
use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetConsumptionTableMap;
use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetTableMap;
use Orm\Zed\PurchasingControl\Persistence\Map\SpyCostCenterTableMap;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlPersistenceFactory getFactory()
 */
class PurchasingControlRepository extends AbstractRepository implements PurchasingControlRepositoryInterface
{
    protected const string COL_CONSUMED_AMOUNT = 'ConsumedAmount';

    protected const string COL_REMAINING_AMOUNT = 'RemainingAmount';

    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer
    {
        $costCenterCollectionTransfer = new CostCenterCollectionTransfer();
        $paginationTransfer = $costCenterCriteriaTransfer->getPagination();

        $costCenterQuery = $this->getFactory()->createCostCenterQuery();

        if ($paginationTransfer === null) {
            $costCenterQuery
                ->leftJoinWithSpyCostCenterToCompanyBusinessUnit()
                ->useSpyCostCenterToCompanyBusinessUnitQuery()
                    ->leftJoinSpyCompanyBusinessUnit()
                ->endUse();
        }

        $costCenterQuery = $this->applyCostCenterConditions($costCenterQuery, $costCenterCriteriaTransfer);

        /** @var \ArrayObject<array-key, \Generated\Shared\Transfer\SortTransfer> $sortTransfers */
        $sortTransfers = $costCenterCriteriaTransfer->getSortCollection();
        $costCenterQuery = $this->applySorting($costCenterQuery, $sortTransfers);

        if ($paginationTransfer !== null) {
            $costCenterQuery = $this->applyPagination($costCenterQuery, $paginationTransfer);
            $costCenterCollectionTransfer->setPagination($paginationTransfer);
        }

        $mapper = $this->getFactory()->createPurchasingControlMapper();

        foreach ($costCenterQuery->find() as $costCenterEntity) {
            $costCenterCollectionTransfer->addCostCenter(
                $mapper->mapCostCenterEntityToTransfer($costCenterEntity, new CostCenterTransfer()),
            );
        }

        return $costCenterCollectionTransfer;
    }

    /**
     * @module CompanyBusinessUnit
     * @module CompanyUser
     */
    protected function applyCostCenterConditions(
        SpyCostCenterQuery $costCenterQuery,
        CostCenterCriteriaTransfer $costCenterCriteriaTransfer,
    ): SpyCostCenterQuery {
        $conditionsTransfer = $costCenterCriteriaTransfer->getCostCenterConditions();

        if ($conditionsTransfer === null) {
            return $costCenterQuery;
        }

        if ($conditionsTransfer->getCostCenterIds() !== []) {
            $costCenterQuery->filterByIdCostCenter_In($conditionsTransfer->getCostCenterIds());
        }

        if ($conditionsTransfer->getUuids() !== []) {
            $costCenterQuery->filterByUuid_In($conditionsTransfer->getUuids());
        }

        if ($conditionsTransfer->getCompanyBusinessUnitIds() !== []) {
            $costCenterQuery->useSpyCostCenterToCompanyBusinessUnitQuery()
                ->filterByFkCompanyBusinessUnit_In($conditionsTransfer->getCompanyBusinessUnitIds())
                ->endUse();
        }

        if ($conditionsTransfer->getCompanyIds() !== []) {
            $costCenterQuery->useSpyCostCenterToCompanyBusinessUnitQuery()
                ->useSpyCompanyBusinessUnitQuery()
                    ->filterByFkCompany_In($conditionsTransfer->getCompanyIds())
                ->endUse()
            ->endUse()
            ->distinct();
        }

        if ($conditionsTransfer->getIsActive() !== null) {
            $costCenterQuery->filterByIsActive($conditionsTransfer->getIsActive());
        }

        if ($conditionsTransfer->getName()) {
            $costCenterQuery->filterByName(sprintf('%%%s%%', $conditionsTransfer->getName()), Criteria::LIKE);
        }

        if ($conditionsTransfer->getCurrencyIsoCodes() !== []) {
            $costCenterQuery->useSpyBudgetQuery('', Criteria::INNER_JOIN)
                ->filterByCurrencyIsoCode_In($conditionsTransfer->getCurrencyIsoCodes())
                ->endUse()
                ->distinct();
        }

        if ($conditionsTransfer->getSalesOrderIds() !== []) {
            $costCenterQuery->addJoin(
                SpyCostCenterTableMap::COL_ID_COST_CENTER,
                SpySalesOrderTableMap::COL_FK_COST_CENTER,
                Criteria::INNER_JOIN,
            );
            $costCenterQuery->add(
                SpySalesOrderTableMap::COL_ID_SALES_ORDER,
                $conditionsTransfer->getSalesOrderIds(),
                Criteria::IN,
            );
        }

        return $costCenterQuery;
    }

    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer
    {
        $budgetCollectionTransfer = new BudgetCollectionTransfer();
        $paginationTransfer = $budgetCriteriaTransfer->getPagination();

        $budgetQuery = $this->getFactory()->createBudgetQuery();

        if ($budgetCriteriaTransfer->getBudgetConditions()?->getWithBudgetConsumption()) {
            $budgetQuery->withColumn(
                sprintf('COALESCE(SUM(%s), 0)', SpyBudgetConsumptionTableMap::COL_AMOUNT),
                static::COL_CONSUMED_AMOUNT,
            )
                ->withColumn(
                    sprintf('COALESCE(%s - COALESCE(SUM(%s), 0), 0)', SpyBudgetTableMap::COL_AMOUNT, SpyBudgetConsumptionTableMap::COL_AMOUNT),
                    static::COL_REMAINING_AMOUNT,
                )
                ->leftJoinSpyBudgetConsumption()
                ->groupByIdBudget();
        }

        $budgetQuery = $this->applyBudgetConditions($budgetQuery, $budgetCriteriaTransfer);

        /** @var \ArrayObject<array-key, \Generated\Shared\Transfer\SortTransfer> $sortTransfers */
        $sortTransfers = $budgetCriteriaTransfer->getSortCollection();
        $budgetQuery = $this->applySorting($budgetQuery, $sortTransfers);

        if ($paginationTransfer !== null) {
            $budgetQuery = $this->applyPagination($budgetQuery, $paginationTransfer);
            $budgetCollectionTransfer->setPagination($paginationTransfer);
        }

        $purchasingControlMapper = $this->getFactory()->createPurchasingControlMapper();

        foreach ($budgetQuery->find() as $budgetEntity) {
            $budgetCollectionTransfer->addBudget(
                $purchasingControlMapper->mapBudgetEntityToTransfer($budgetEntity, new BudgetTransfer()),
            );
        }

        return $budgetCollectionTransfer;
    }

    protected function applyBudgetConditions(SpyBudgetQuery $budgetQuery, BudgetCriteriaTransfer $budgetCriteriaTransfer): SpyBudgetQuery
    {
        $conditionsTransfer = $budgetCriteriaTransfer->getBudgetConditions();

        if ($conditionsTransfer === null) {
            return $budgetQuery;
        }

        if ($conditionsTransfer->getNames() !== []) {
            $budgetQuery->filterByName_In($conditionsTransfer->getNames());
        }

        if ($conditionsTransfer->getEnforcementRules() !== []) {
            $budgetQuery->filterByEnforcementRule_In($conditionsTransfer->getEnforcementRules());
        }

        if ($conditionsTransfer->getBudgetIds() !== []) {
            $budgetQuery->filterByIdBudget_In($conditionsTransfer->getBudgetIds());
        }

        if ($conditionsTransfer->getUuids() !== []) {
            $budgetQuery->filterByUuid_In($conditionsTransfer->getUuids());
        }

        if ($conditionsTransfer->getCostCenterIds() !== []) {
            $budgetQuery->filterByFkCostCenter_In($conditionsTransfer->getCostCenterIds());
        }

        if ($conditionsTransfer->getIsActive() !== null) {
            $budgetQuery->filterByIsActive($conditionsTransfer->getIsActive());
        }

        if ($conditionsTransfer->getCurrencyIsoCodes() !== []) {
            $budgetQuery->filterByCurrencyIsoCode_In($conditionsTransfer->getCurrencyIsoCodes());
        }

        if ($conditionsTransfer->getActiveOnDate() !== null) {
            $budgetQuery->filterByStartsAt($conditionsTransfer->getActiveOnDate(), Criteria::LESS_EQUAL)
                ->filterByEndsAt($conditionsTransfer->getActiveOnDate(), Criteria::GREATER_EQUAL);
        }

        if ($conditionsTransfer->getStartsAtFrom() !== null) {
            $budgetQuery->filterByStartsAt($conditionsTransfer->getStartsAtFrom(), Criteria::GREATER_EQUAL);
        }

        if ($conditionsTransfer->getEndsAtTo() !== null) {
            $budgetQuery->filterByEndsAt($conditionsTransfer->getEndsAtTo(), Criteria::LESS_EQUAL);
        }

        return $budgetQuery;
    }

    /**
     * @param \ArrayObject<array-key, \Generated\Shared\Transfer\SortTransfer> $sortTransfers
     */
    protected function applySorting(ModelCriteria $query, ArrayObject $sortTransfers): ModelCriteria
    {
        foreach ($sortTransfers as $sortTransfer) {
            $query->orderBy(
                $sortTransfer->getFieldOrFail(),
                $sortTransfer->getIsAscending() ? Criteria::ASC : Criteria::DESC,
            );
        }

        return $query;
    }

    public function getBudgetConsumptionCollection(BudgetConsumptionCriteriaTransfer $budgetConsumptionCriteriaTransfer): BudgetConsumptionCollectionTransfer
    {
        $budgetConsumptionCollectionTransfer = new BudgetConsumptionCollectionTransfer();
        $budgetConsumptionQuery = $this->getFactory()->createBudgetConsumptionQuery();
        $conditionsTransfer = $budgetConsumptionCriteriaTransfer->getBudgetConsumptionConditions();

        if ($conditionsTransfer !== null && $conditionsTransfer->getSalesOrderIds() !== []) {
            $budgetConsumptionQuery->filterByFkSalesOrder_In($conditionsTransfer->getSalesOrderIds());
        }

        $purchasingControlMapper = $this->getFactory()->createPurchasingControlMapper();

        foreach ($budgetConsumptionQuery->find() as $budgetConsumptionEntity) {
            $budgetConsumptionCollectionTransfer->addBudgetConsumption(
                $purchasingControlMapper->mapBudgetConsumptionEntityToTransfer($budgetConsumptionEntity, new BudgetConsumptionTransfer()),
            );
        }

        return $budgetConsumptionCollectionTransfer;
    }

    /**
     * @module CompanyBusinessUnit
     *
     * @param array<int> $companyBusinessUnitIds
     *
     * @return array<int>
     */
    public function getCompanyBusinessUnitIdsForCompany(int $idCompany, array $companyBusinessUnitIds): array
    {
        $companyBusinessUnitIds = $this->getFactory()
            ->getCompanyBusinessUnitQuery()
            ->filterByIdCompanyBusinessUnit_In($companyBusinessUnitIds)
            ->filterByFkCompany($idCompany)
            ->select(SpyCompanyBusinessUnitTableMap::COL_ID_COMPANY_BUSINESS_UNIT)
            ->find()
            ->getData();

        return array_values($companyBusinessUnitIds);
    }

    protected function applyPagination(ModelCriteria $query, PaginationTransfer $paginationTransfer): ModelCriteria
    {
        if ($paginationTransfer->getOffset() !== null && $paginationTransfer->getLimit() !== null) {
            $paginationTransfer->setNbResults($query->count());

            return $query
                ->offset($paginationTransfer->getOffsetOrFail())
                ->setLimit($paginationTransfer->getLimitOrFail());
        }

        if ($paginationTransfer->getPage() !== null && $paginationTransfer->getMaxPerPage() !== null) {
            $propelModelPager = $query->paginate(
                $paginationTransfer->getPageOrFail(),
                $paginationTransfer->getMaxPerPageOrFail(),
            );

            $paginationTransfer->setNbResults($propelModelPager->getNbResults())
                ->setFirstIndex($propelModelPager->getFirstIndex())
                ->setLastIndex($propelModelPager->getLastIndex())
                ->setFirstPage($propelModelPager->getFirstPage())
                ->setLastPage($propelModelPager->getLastPage())
                ->setNextPage($propelModelPager->getNextPage())
                ->setPreviousPage($propelModelPager->getPreviousPage());

            return $propelModelPager->getQuery();
        }

        return $query;
    }
}

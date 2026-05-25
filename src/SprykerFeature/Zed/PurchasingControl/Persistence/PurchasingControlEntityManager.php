<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudget;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumption;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenter;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnit;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlPersistenceFactory getFactory()
 */
class PurchasingControlEntityManager extends AbstractEntityManager implements PurchasingControlEntityManagerInterface
{
    protected const string BUDGET_PHP_NAME_IS_ACTIVE = 'IsActive';

    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        return $this->saveCostCenter($costCenterTransfer, new SpyCostCenter());
    }

    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        $costCenterEntity = $this->getFactory()->createCostCenterQuery()
            ->findPk($costCenterTransfer->getIdCostCenterOrFail());

        return $this->saveCostCenter($costCenterTransfer, $costCenterEntity);
    }

    public function createBudget(BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        return $this->saveBudget($budgetTransfer, new SpyBudget());
    }

    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        $budgetEntity = $this->getFactory()->createBudgetQuery()
            ->findPk($budgetTransfer->getIdBudgetOrFail());

        return $this->saveBudget($budgetTransfer, $budgetEntity);
    }

    public function createBudgetConsumption(BudgetConsumptionTransfer $budgetConsumptionTransfer): BudgetConsumptionTransfer
    {
        return $this->saveBudgetConsumption($budgetConsumptionTransfer, new SpyBudgetConsumption());
    }

    public function updateBudgetConsumption(BudgetConsumptionTransfer $budgetConsumptionTransfer): BudgetConsumptionTransfer
    {
        $budgetConsumptionEntity = $this->getFactory()->createBudgetConsumptionQuery()
            ->findPk($budgetConsumptionTransfer->getIdBudgetConsumptionOrFail());

        return $this->saveBudgetConsumption($budgetConsumptionTransfer, $budgetConsumptionEntity);
    }

    public function deleteBudgetConsumptionByIdSalesOrder(int $idSalesOrder): void
    {
        $this->getFactory()->createBudgetConsumptionQuery()
            ->filterByFkSalesOrder($idSalesOrder)
            ->delete();
    }

    public function deactivateBudgetsByCostCenterId(int $idCostCenter): void
    {
        $this->getFactory()
            ->createBudgetQuery()
            ->filterByFkCostCenter($idCostCenter)
            ->filterByIsActive(true)
            ->update([static::BUDGET_PHP_NAME_IS_ACTIVE => false]);
    }

    public function updateSalesOrderCostCenter(int $idSalesOrder, int $idCostCenter, ?int $idBudget): void
    {
        SpySalesOrderQuery::create()
            ->filterByIdSalesOrder($idSalesOrder)
            ->findOne()
            ?->setFkCostCenter($idCostCenter)
            ->setFkBudget($idBudget)
            ->save();
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     */
    protected function syncBusinessUnitAssignments(int $idCostCenter, array $companyBusinessUnitIds): void
    {
        $this->getFactory()->createCostCenterToCompanyBusinessUnitQuery()
            ->filterByFkCostCenter($idCostCenter)
            ->delete();

        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $costCenterToCompanyBusinessUnitEntity = new SpyCostCenterToCompanyBusinessUnit();
            $costCenterToCompanyBusinessUnitEntity->setFkCostCenter($idCostCenter);
            $costCenterToCompanyBusinessUnitEntity->setFkCompanyBusinessUnit($idCompanyBusinessUnit);
            $costCenterToCompanyBusinessUnitEntity->save();
        }
    }

    protected function saveCostCenter(CostCenterTransfer $costCenterTransfer, SpyCostCenter $costCenterEntity): CostCenterTransfer
    {
        $purchasingControlMapper = $this->getFactory()->createPurchasingControlMapper();
        $costCenterEntity = $purchasingControlMapper->mapCostCenterTransferToEntity($costCenterTransfer, $costCenterEntity);
        $costCenterEntity->save();

        $this->syncBusinessUnitAssignments($costCenterEntity->getIdCostCenter(), $costCenterTransfer->getCompanyBusinessUnitIds());

        $costCenterTransfer->setIdCostCenter($costCenterEntity->getIdCostCenter());

        return $costCenterTransfer;
    }

    protected function saveBudgetConsumption(
        BudgetConsumptionTransfer $budgetConsumptionTransfer,
        SpyBudgetConsumption $budgetConsumptionEntity
    ): BudgetConsumptionTransfer {
        $purchasingControlMapper = $this->getFactory()->createPurchasingControlMapper();
        $budgetConsumptionEntity = $purchasingControlMapper->mapBudgetConsumptionTransferToEntity($budgetConsumptionTransfer, $budgetConsumptionEntity);
        $budgetConsumptionEntity->save();

        $budgetConsumptionTransfer->setIdBudgetConsumption($budgetConsumptionEntity->getIdBudgetConsumption());

        return $budgetConsumptionTransfer;
    }

    protected function saveBudget(BudgetTransfer $budgetTransfer, SpyBudget $budgetEntity): BudgetTransfer
    {
        $purchasingControlMapper = $this->getFactory()->createPurchasingControlMapper();
        $budgetEntity = $purchasingControlMapper->mapBudgetTransferToEntity($budgetTransfer, $budgetEntity);
        $budgetEntity->save();

        return $purchasingControlMapper->mapBudgetEntityToTransfer($budgetEntity, $budgetTransfer);
    }
}

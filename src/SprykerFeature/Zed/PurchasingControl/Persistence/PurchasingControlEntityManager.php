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
    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        $mapper = $this->getFactory()->createCostCenterMapper();
        $costCenterEntity = $mapper->mapCostCenterTransferToEntity($costCenterTransfer, new SpyCostCenter());
        $costCenterEntity->save();

        $this->syncBusinessUnitAssignments($costCenterEntity->getIdCostCenter(), $costCenterTransfer->getCompanyBusinessUnitIds());

        $costCenterTransfer->setIdCostCenter($costCenterEntity->getIdCostCenter());

        return $costCenterTransfer;
    }

    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        $costCenterEntity = $this->getFactory()->createCostCenterQuery()
            ->findPk($costCenterTransfer->getIdCostCenterOrFail());

        $mapper = $this->getFactory()->createCostCenterMapper();
        $costCenterEntity = $mapper->mapCostCenterTransferToEntity($costCenterTransfer, $costCenterEntity);
        $costCenterEntity->save();

        $this->syncBusinessUnitAssignments($costCenterEntity->getIdCostCenter(), $costCenterTransfer->getCompanyBusinessUnitIds());

        return $costCenterTransfer;
    }

    public function createBudget(BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        $mapper = $this->getFactory()->createCostCenterMapper();
        $budgetEntity = $mapper->mapBudgetTransferToEntity($budgetTransfer, new SpyBudget());
        $budgetEntity->save();

        return $mapper->mapBudgetEntityToTransfer($budgetEntity, $budgetTransfer);
    }

    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        $budgetEntity = $this->getFactory()->createBudgetQuery()
            ->findPk($budgetTransfer->getIdBudgetOrFail());

        $mapper = $this->getFactory()->createCostCenterMapper();
        $budgetEntity = $mapper->mapBudgetTransferToEntity($budgetTransfer, $budgetEntity);
        $budgetEntity->save();

        return $mapper->mapBudgetEntityToTransfer($budgetEntity, $budgetTransfer);
    }

    public function createBudgetConsumption(BudgetConsumptionTransfer $budgetConsumptionTransfer): BudgetConsumptionTransfer
    {
        $mapper = $this->getFactory()->createCostCenterMapper();
        $budgetConsumptionEntity = $mapper->mapBudgetConsumptionTransferToEntity($budgetConsumptionTransfer, new SpyBudgetConsumption());
        $budgetConsumptionEntity->save();

        $budgetConsumptionTransfer->setIdBudgetConsumption($budgetConsumptionEntity->getIdBudgetConsumption());

        return $budgetConsumptionTransfer;
    }

    public function deleteBudgetConsumptionByIdSalesOrder(int $idSalesOrder): void
    {
        $this->getFactory()->createBudgetConsumptionQuery()
            ->filterByFkSalesOrder($idSalesOrder)
            ->delete();
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
        // Delete all existing junction records and re-insert — simplest correct approach for a multi-select
        $this->getFactory()->createCostCenterToCompanyBusinessUnitQuery()
            ->filterByFkCostCenter($idCostCenter)
            ->delete();

        foreach ($companyBusinessUnitIds as $idCompanyBusinessUnit) {
            $junctionEntity = new SpyCostCenterToCompanyBusinessUnit();
            $junctionEntity->setFkCostCenter($idCostCenter);
            $junctionEntity->setFkCompanyBusinessUnit($idCompanyBusinessUnit);
            $junctionEntity->save();
        }
    }
}

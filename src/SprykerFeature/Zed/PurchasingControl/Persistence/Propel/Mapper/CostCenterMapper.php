<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudget;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumption;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenter;

class CostCenterMapper
{
    public function mapCostCenterEntityToTransfer(SpyCostCenter $costCenterEntity, CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        $costCenterTransfer->fromArray($costCenterEntity->toArray(), true);

        foreach ($costCenterEntity->getSpyCostCenterToCompanyBusinessUnits() as $junction) {
            $costCenterTransfer->addIdCompanyBusinessUnit($junction->getFkCompanyBusinessUnit());

            $businessUnit = $junction->getSpyCompanyBusinessUnit();
            if ($businessUnit !== null) {
                $costCenterTransfer->addCompanyBusinessUnit(
                    (new CompanyBusinessUnitTransfer())
                        ->setIdCompanyBusinessUnit($businessUnit->getIdCompanyBusinessUnit())
                        ->setName($businessUnit->getName()),
                );
            }
        }

        return $costCenterTransfer;
    }

    public function mapCostCenterTransferToEntity(CostCenterTransfer $costCenterTransfer, SpyCostCenter $costCenterEntity): SpyCostCenter
    {
        $costCenterEntity->setName($costCenterTransfer->getNameOrFail());
        $costCenterEntity->setDescription($costCenterTransfer->getDescription());
        $costCenterEntity->setIsActive((bool)$costCenterTransfer->getIsActive());

        return $costCenterEntity;
    }

    public function mapBudgetEntityToTransfer(SpyBudget $budgetEntity, BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        $budgetTransfer->fromArray($budgetEntity->toArray(), true);
        $budgetTransfer->setIdCostCenter($budgetEntity->getFkCostCenter());

        $budgetArray = $budgetEntity->toArray();

        if (isset($budgetArray['ConsumedAmount'])) {
            $consumedAmount = (int)$budgetArray['ConsumedAmount'];
            $budgetTransfer->setConsumedAmount($consumedAmount);
            $budgetTransfer->setRemainingAmount($budgetTransfer->getAmountOrFail() - $consumedAmount);
        }

        return $budgetTransfer;
    }

    public function mapBudgetTransferToEntity(BudgetTransfer $budgetTransfer, SpyBudget $budgetEntity): SpyBudget
    {
        $budgetEntity->fromArray($budgetTransfer->modifiedToArray());
        $budgetEntity->setFkCostCenter($budgetTransfer->getIdCostCenterOrFail());
        $budgetEntity->setIsActive((bool)$budgetTransfer->getIsActive());

        return $budgetEntity;
    }

    public function mapBudgetConsumptionTransferToEntity(
        BudgetConsumptionTransfer $budgetConsumptionTransfer,
        SpyBudgetConsumption $budgetConsumptionEntity
    ): SpyBudgetConsumption {
        $budgetConsumptionEntity->setFkBudget($budgetConsumptionTransfer->getIdBudgetOrFail());
        $budgetConsumptionEntity->setFkSalesOrder($budgetConsumptionTransfer->getIdSalesOrderOrFail());
        $budgetConsumptionEntity->setAmount($budgetConsumptionTransfer->getAmountOrFail());

        return $budgetConsumptionEntity;
    }
}

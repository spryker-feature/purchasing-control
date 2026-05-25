<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Shared\PurchasingControl\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\BudgetBuilder;
use Generated\Shared\DataBuilder\CostCenterBuilder;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudget;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumption;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenter;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnit;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnitQuery;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class PurchasingControlHelper extends Module
{
    use DataCleanupHelperTrait;
    use LocatorHelperTrait;

    protected const string OVERRIDE_COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected const string OVERRIDE_COMPANY_NAME = 'companyName';

    protected const string OVERRIDE_BUSINESS_UNIT_NAME = 'name';

    protected const string DATE_FORMAT = 'Y-m-d';

    /**
     * @param array<string, mixed> $overrides
     */
    public function haveCostCenter(array $overrides = []): CostCenterTransfer
    {
        $companyBusinessUnitTransfer = $overrides[static::OVERRIDE_COMPANY_BUSINESS_UNIT]
            ?? $this->haveCompanyBusinessUnit();

        unset($overrides[static::OVERRIDE_COMPANY_BUSINESS_UNIT]);

        $costCenterTransfer = (new CostCenterBuilder($overrides))->build()
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterTransfer = $this->persistCostCenter($costCenterTransfer);
        $this->scheduleCostCenterCleanup($costCenterTransfer->getIdCostCenterOrFail());

        return $costCenterTransfer;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function haveBudget(int $idCostCenter, array $overrides = []): BudgetTransfer
    {
        $budgetTransfer = (new BudgetBuilder($this->buildBudgetDefaults($overrides)))->build()
            ->setIdCostCenter($idCostCenter);

        $budgetTransfer = $this->persistBudget($budgetTransfer);
        $this->scheduleBudgetCleanup($budgetTransfer->getIdBudgetOrFail());

        return $budgetTransfer;
    }

    public function haveBudgetConsumption(int $idBudget, int $idSalesOrder, int $amount): void
    {
        $budgetConsumptionEntity = (new SpyBudgetConsumption())
            ->setFkBudget($idBudget)
            ->setFkSalesOrder($idSalesOrder)
            ->setAmount($amount);

        $budgetConsumptionEntity->save();

        $this->scheduleBudgetConsumptionCleanup($budgetConsumptionEntity->getIdBudgetConsumption());
    }

    public function ensurePurchasingControlTablesAreEmpty(): void
    {
        SpyBudgetConsumptionQuery::create()->deleteAll();
        SpyBudgetQuery::create()->deleteAll();
        SpyCostCenterToCompanyBusinessUnitQuery::create()->deleteAll();
        SpyCostCenterQuery::create()->deleteAll();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function haveCompanyBusinessUnit(array $overrides = []): CompanyBusinessUnitTransfer
    {
        $companyTransfer = $this->getLocator()->company()->facade()->create(
            (new CompanyTransfer())
                ->setName($overrides[static::OVERRIDE_COMPANY_NAME] ?? sprintf('Test Company %s', uniqid()))
                ->setIsActive(true),
        )->getCompanyTransfer();

        return $this->getLocator()->companyBusinessUnit()->facade()->create(
            (new CompanyBusinessUnitTransfer())
                ->setName($overrides[static::OVERRIDE_BUSINESS_UNIT_NAME] ?? sprintf('Test BU %s', uniqid()))
                ->setFkCompany($companyTransfer->getIdCompanyOrFail()),
        )->getCompanyBusinessUnitTransfer();
    }

    protected function persistCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterTransfer
    {
        $costCenterEntity = new SpyCostCenter();
        $costCenterEntity->setName($costCenterTransfer->getNameOrFail());
        $costCenterEntity->setIsActive((bool)$costCenterTransfer->getIsActive());
        $costCenterEntity->save();

        foreach ($costCenterTransfer->getCompanyBusinessUnitIds() as $idCompanyBusinessUnit) {
            (new SpyCostCenterToCompanyBusinessUnit())
                ->setFkCostCenter($costCenterEntity->getIdCostCenter())
                ->setFkCompanyBusinessUnit($idCompanyBusinessUnit)
                ->save();
        }

        return $costCenterTransfer
            ->setIdCostCenter($costCenterEntity->getIdCostCenter())
            ->setUuid($costCenterEntity->getUuid());
    }

    protected function scheduleCostCenterCleanup(int $idCostCenter): void
    {
        $this->getDataCleanupHelper()->_addCleanup(function () use ($idCostCenter): void {
            SpyBudgetConsumptionQuery::create()->useSpyBudgetQuery()->filterByFkCostCenter($idCostCenter)->endUse()->delete();
            SpyBudgetQuery::create()->filterByFkCostCenter($idCostCenter)->delete();
            SpyCostCenterToCompanyBusinessUnitQuery::create()->filterByFkCostCenter($idCostCenter)->delete();
            SpyCostCenterQuery::create()->filterByIdCostCenter($idCostCenter)->delete();
        });
    }

    protected function persistBudget(BudgetTransfer $budgetTransfer): BudgetTransfer
    {
        $budgetEntity = new SpyBudget();
        $budgetEntity->fromArray($budgetTransfer->modifiedToArray());
        $budgetEntity->setFkCostCenter($budgetTransfer->getIdCostCenterOrFail());
        $budgetEntity->save();

        return $budgetTransfer
            ->setIdBudget($budgetEntity->getIdBudget())
            ->setUuid($budgetEntity->getUuid());
    }

    protected function scheduleBudgetCleanup(int $idBudget): void
    {
        $this->getDataCleanupHelper()->_addCleanup(function () use ($idBudget): void {
            SpyBudgetConsumptionQuery::create()->filterByFkBudget($idBudget)->delete();
            SpyBudgetQuery::create()->filterByIdBudget($idBudget)->delete();
        });
    }

    protected function scheduleBudgetConsumptionCleanup(int $idBudgetConsumption): void
    {
        $this->getDataCleanupHelper()->_addCleanup(function () use ($idBudgetConsumption): void {
            SpyBudgetConsumptionQuery::create()->filterByIdBudgetConsumption($idBudgetConsumption)->delete();
        });
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    protected function buildBudgetDefaults(array $overrides): array
    {
        return array_merge([
            BudgetTransfer::STARTS_AT => date(static::DATE_FORMAT, strtotime('-1 day')),
            BudgetTransfer::ENDS_AT => date(static::DATE_FORMAT, strtotime('+30 days')),
        ], $overrides);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group CostCenter
 * @group Business
 * @group PurchasingControlFacadeTest
 */
class PurchasingControlFacadeTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    public function testCreateCostCenterPersistsCostCenter(): void
    {
        $costCenterTransfer = $this->tester->buildCostCenterTransfer();

        $costCenterResponseTransfer = $this->tester->getFacade()->createCostCenter($costCenterTransfer);

        $this->assertTrue($costCenterResponseTransfer->getIsSuccessful());
        $this->assertNotNull($costCenterResponseTransfer->getCostCenter()->getIdCostCenter());
        $this->assertSame($costCenterTransfer->getName(), $costCenterResponseTransfer->getCostCenter()->getName());
    }

    public function testUpdateCostCenterChangesName(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $costCenterTransfer->setName('Updated Name');

        $costCenterResponseTransfer = $this->tester->getFacade()->updateCostCenter($costCenterTransfer);

        $this->assertTrue($costCenterResponseTransfer->getIsSuccessful());
        $this->assertSame('Updated Name', $costCenterResponseTransfer->getCostCenter()->getName());
    }

    public function testGetCostCenterCollectionFiltersByBusinessUnit(): void
    {
        $idCompanyBusinessUnit = $this->tester->haveCompanyBusinessUnit()->getIdCompanyBusinessUnitOrFail();
        $this->tester->haveCostCenter(['idCompanyBusinessUnit' => $idCompanyBusinessUnit]);
        $this->tester->haveCostCenter(['idCompanyBusinessUnit' => $this->tester->haveCompanyBusinessUnit()->getIdCompanyBusinessUnitOrFail()]);

        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit);

        $collection = $this->tester->getFacade()->getCostCenterCollection($criteriaTransfer);

        $this->assertCount(1, $collection->getCostCenters());
        $this->assertContains($idCompanyBusinessUnit, $collection->getCostCenters()->offsetGet(0)->getCompanyBusinessUnitIds());
    }

    public function testGetCostCenterCollectionFiltersByIsActive(): void
    {
        $this->tester->haveCostCenter(['isActive' => true]);
        $this->tester->haveCostCenter(['isActive' => false]);

        $criteriaTransfer = (new CostCenterCriteriaTransfer())->setIsActive(true);

        $collection = $this->tester->getFacade()->getCostCenterCollection($criteriaTransfer);

        foreach ($collection->getCostCenters() as $costCenter) {
            $this->assertTrue($costCenter->getIsActive());
        }
    }

    public function testCreateBudgetPersistsBudget(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenter->getIdCostCenterOrFail());

        $budgetResponseTransfer = $this->tester->getFacade()->createBudget($budgetTransfer);

        $this->assertTrue($budgetResponseTransfer->getIsSuccessful());
        $this->assertNotNull($budgetResponseTransfer->getBudget()->getIdBudget());
        $this->assertSame($budgetTransfer->getName(), $budgetResponseTransfer->getBudget()->getName());
    }

    public function testUpdateBudgetChangesEnforcementRule(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $budgetTransfer->setEnforcementRule('warn');

        $budgetResponseTransfer = $this->tester->getFacade()->updateBudget($budgetTransfer);

        $this->assertTrue($budgetResponseTransfer->getIsSuccessful());
        $this->assertSame('warn', $budgetResponseTransfer->getBudget()->getEnforcementRule());
    }

    public function testGetActiveBudgetsFiltersExpiredBudgets(): void
    {
        $costCenter = $this->tester->haveCostCenter();

        // Expired budget (ends in the past)
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => '2020-01-01',
            'endsAt' => '2020-12-31',
            'currencyIsoCode' => 'EUR',
            'isActive' => true,
        ]);

        $collection = $this->tester->getFacade()->getActiveBudgetsForCostCenter(
            $costCenter->getIdCostCenterOrFail(),
            'EUR',
        );

        $this->assertCount(0, $collection->getBudgets());
    }

    public function testGetActiveBudgetsFiltersInactiveBudgets(): void
    {
        $costCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => date('Y-m-d', strtotime('-10 days')),
            'endsAt' => date('Y-m-d', strtotime('+10 days')),
            'currencyIsoCode' => 'EUR',
            'isActive' => false,
        ]);

        $collection = $this->tester->getFacade()->getActiveBudgetsForCostCenter(
            $costCenter->getIdCostCenterOrFail(),
            'EUR',
        );

        $this->assertCount(0, $collection->getBudgets());
    }

    public function testGetActiveBudgetsFiltersByCurrency(): void
    {
        $costCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => date('Y-m-d', strtotime('-10 days')),
            'endsAt' => date('Y-m-d', strtotime('+10 days')),
            'currencyIsoCode' => 'USD',
            'isActive' => true,
        ]);

        $collection = $this->tester->getFacade()->getActiveBudgetsForCostCenter(
            $costCenter->getIdCostCenterOrFail(),
            'EUR',
        );

        $this->assertCount(0, $collection->getBudgets());
    }
}

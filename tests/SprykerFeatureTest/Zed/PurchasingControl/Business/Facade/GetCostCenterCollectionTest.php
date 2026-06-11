<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group Facade
 * @group GetCostCenterCollectionTest
 * Add your own group annotations below this line
 */
class GetCostCenterCollectionTest extends Unit
{
    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testReturnsAllCostCentersWithoutConditions(): void
    {
        // Arrange
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = new CostCenterCriteriaTransfer();

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(3, $costCenterCollectionTransfer->getCostCenters());
        $this->assertNull($costCenterCollectionTransfer->getPagination());
    }

    public function testReturnsCostCentersByCompanyBusinessUnitId(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $idTargetBusinessUnit = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);
        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCompanyBusinessUnit($idTargetBusinessUnit),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();

        $this->assertContains($idTargetBusinessUnit, $retrievedCostCenter->getCompanyBusinessUnitIds());
        $this->assertNull($costCenterCollectionTransfer->getPagination());
    }

    public function testReturnsCostCentersByMultipleCompanyBusinessUnitIds(): void
    {
        // Arrange
        $companyTransfer1 = $this->tester->haveCompany();
        $companyBusinessUnit1 = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer1->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer1,
        ]);
        $companyTransfer2 = $this->tester->haveCompany();
        $companyBusinessUnit2 = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer2->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer2,
        ]);
        $idBusinessUnit1 = $companyBusinessUnit1->getIdCompanyBusinessUnitOrFail();
        $idBusinessUnit2 = $companyBusinessUnit2->getIdCompanyBusinessUnitOrFail();
        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnit1]);
        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnit2]);
        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addIdCompanyBusinessUnit($idBusinessUnit1)
                    ->addIdCompanyBusinessUnit($idBusinessUnit2),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(2, $costCenterCollectionTransfer->getCostCenters());
    }

    public function testReturnsCostCentersByIsActiveTrue(): void
    {
        // Arrange
        $this->tester->haveCostCenter([CostCenterTransfer::IS_ACTIVE => true]);
        $this->tester->haveCostCenter([CostCenterTransfer::IS_ACTIVE => true]);
        $this->tester->haveCostCenter([CostCenterTransfer::IS_ACTIVE => false]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->setIsActive(true),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(2, $costCenterCollectionTransfer->getCostCenters());

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $this->assertTrue($costCenterTransfer->getIsActive());
        }
    }

    public function testReturnsCostCentersByIsActiveFalse(): void
    {
        // Arrange
        $this->tester->haveCostCenter([CostCenterTransfer::IS_ACTIVE => true]);
        $this->tester->haveCostCenter([CostCenterTransfer::IS_ACTIVE => false]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->setIsActive(false),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertFalse($retrievedCostCenter->getIsActive());
    }

    public function testReturnsCostCentersByCurrencyIsoCode(): void
    {
        // Arrange
        $costCenterWithEurBudgetTransfer = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenterWithEurBudgetTransfer->getIdCostCenterOrFail(), [
            'currencyIsoCode' => 'EUR',
            'isActive' => true,
            'startsAt' => date('Y-m-d', strtotime('-10 days')),
            'endsAt' => date('Y-m-d', strtotime('+10 days')),
        ]);

        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addCurrencyIsoCode('EUR')
                    ->setWithBudgets(true)
                    ->setBudgetActiveOnDate(date('Y-m-d')),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert — only the cost center with a matching EUR budget is returned
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenterTransfer */
        $retrievedCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertSame($costCenterWithEurBudgetTransfer->getIdCostCenterOrFail(), $retrievedCostCenterTransfer->getIdCostCenterOrFail());
        $this->assertCount(1, $retrievedCostCenterTransfer->getBudgets());
    }

    public function testReturnsCostCentersExcludesInactiveBudgetFromCurrencyFilter(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            'currencyIsoCode' => 'EUR',
            'isActive' => false,
            'startsAt' => date('Y-m-d', strtotime('-10 days')),
            'endsAt' => date('Y-m-d', strtotime('+10 days')),
        ]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addCurrencyIsoCode('EUR')
                    ->setWithBudgets(true)
                    ->setBudgetActiveOnDate(date('Y-m-d')),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert — cost center is returned but inactive budget is excluded from expansion
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenterTransfer */
        $retrievedCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertCount(0, $retrievedCostCenterTransfer->getBudgets());
    }

    public function testReturnsCostCentersExcludesExpiredBudgetFromCurrencyFilter(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            'currencyIsoCode' => 'EUR',
            'isActive' => true,
            'startsAt' => '2020-01-01',
            'endsAt' => '2020-12-31',
        ]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addCurrencyIsoCode('EUR')
                    ->setWithBudgets(true)
                    ->setBudgetActiveOnDate(date('Y-m-d')),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert — cost center is returned but expired budget is excluded from expansion
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenterTransfer */
        $retrievedCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertCount(0, $retrievedCostCenterTransfer->getBudgets());
    }

    public function testFiltersConditionsWithAndLogic(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $idBusinessUnit = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => false,
        ]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addIdCompanyBusinessUnit($idBusinessUnit)
                    ->setIsActive(true),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertTrue($retrievedCostCenter->getIsActive());
        $this->assertContains($idBusinessUnit, $retrievedCostCenter->getCompanyBusinessUnitIds());
    }

    public function testReturnsEmptyCollectionWhenNoCostCentersMatch(): void
    {
        // Arrange
        $this->tester->haveCostCenter();
        $nonExistentBusinessUnitId = 2147483647;

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCompanyBusinessUnit($nonExistentBusinessUnitId),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionTransfer->getCostCenters());
    }

    public function testReturnsCostCentersByCompanyId(): void
    {
        // Arrange
        $companyTransferA = $this->tester->haveCompany();
        $companyBusinessUnitOfCompanyA = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransferA->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransferA,
        ]);
        $companyTransferB = $this->tester->haveCompany();
        $companyBusinessUnitOfCompanyB = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransferB->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransferB,
        ]);
        $idCompanyA = $companyTransferA->getIdCompanyOrFail();
        $idBusinessUnitA = $companyBusinessUnitOfCompanyA->getIdCompanyBusinessUnitOrFail();

        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitOfCompanyA]);
        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitOfCompanyB]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCompany($idCompanyA),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenterTransfer */
        $retrievedCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertContains($idBusinessUnitA, $retrievedCostCenterTransfer->getCompanyBusinessUnitIds());
    }

    public function testReturnsCostCentersByLimitAndOffset(): void
    {
        // Arrange
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $paginationTransfer = (new PaginationTransfer())
            ->setOffset(1)
            ->setLimit(2);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setPagination($paginationTransfer);

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(2, $costCenterCollectionTransfer->getCostCenters());
        $this->assertNotNull($costCenterCollectionTransfer->getPagination());
        $this->assertSame(4, $costCenterCollectionTransfer->getPaginationOrFail()->getNbResultsOrFail());
    }

    public function testReturnsCostCentersByPageAndMaxPerPage(): void
    {
        // Arrange
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $paginationTransfer = (new PaginationTransfer())
            ->setPage(2)
            ->setMaxPerPage(2);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setPagination($paginationTransfer);

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(2, $costCenterCollectionTransfer->getCostCenters());
        $this->assertNotNull($costCenterCollectionTransfer->getPagination());

        $paginationTransfer = $costCenterCollectionTransfer->getPaginationOrFail();

        $this->assertSame(2, $paginationTransfer->getPageOrFail());
        $this->assertSame(2, $paginationTransfer->getMaxPerPageOrFail());
        $this->assertSame(7, $paginationTransfer->getNbResultsOrFail());
        $this->assertSame(3, $paginationTransfer->getFirstIndexOrFail());
        $this->assertSame(4, $paginationTransfer->getLastIndexOrFail());
        $this->assertSame(1, $paginationTransfer->getFirstPage());
        $this->assertSame(4, $paginationTransfer->getLastPageOrFail());
        $this->assertSame(3, $paginationTransfer->getNextPageOrFail());
        $this->assertSame(1, $paginationTransfer->getPreviousPageOrFail());
    }

    public function testReturnsCostCentersSortedByNameAscending(): void
    {
        // Arrange
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Gamma']);
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Alpha']);
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Beta']);

        $sortTransfer = (new SortTransfer())
            ->setField(CostCenterTransfer::NAME)
            ->setIsAscending(true);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addSort($sortTransfer);

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        $costCenters = $costCenterCollectionTransfer->getCostCenters();

        // Assert
        $this->assertCount(3, $costCenters);
        $this->assertSame('Alpha', $costCenters->offsetGet(0)->getNameOrFail());
        $this->assertSame('Beta', $costCenters->offsetGet(1)->getNameOrFail());
        $this->assertSame('Gamma', $costCenters->offsetGet(2)->getNameOrFail());
    }

    public function testReturnsCostCentersSortedByNameDescending(): void
    {
        // Arrange
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Alpha']);
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Beta']);
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Gamma']);

        $sortTransfer = (new SortTransfer())
            ->setField(CostCenterTransfer::NAME)
            ->setIsAscending(false);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addSort($sortTransfer);

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        $costCenters = $costCenterCollectionTransfer->getCostCenters();

        // Assert
        $this->assertCount(3, $costCenters);
        $this->assertSame('Gamma', $costCenters->offsetGet(0)->getNameOrFail());
        $this->assertSame('Beta', $costCenters->offsetGet(1)->getNameOrFail());
        $this->assertSame('Alpha', $costCenters->offsetGet(2)->getNameOrFail());
    }

    public function testReturnsCostCentersByUuid(): void
    {
        // Arrange
        $targetCostCenter = $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addUuid($targetCostCenter->getUuidOrFail()),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertSame($targetCostCenter->getUuidOrFail(), $retrievedCostCenter->getUuidOrFail());
    }

    public function testReturnsCostCentersByCostCenterIds(): void
    {
        // Arrange
        $targetCostCenter = $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCostCenter($targetCostCenter->getIdCostCenterOrFail()),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertSame($targetCostCenter->getIdCostCenterOrFail(), $retrievedCostCenter->getIdCostCenterOrFail());
    }

    public function testReturnsCostCentersByNameLike(): void
    {
        // Arrange
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Marketing Team']);
        $this->tester->haveCostCenter([CostCenterTransfer::NAME => 'Operations']);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->setName('arket'),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertSame('Marketing Team', $retrievedCostCenter->getNameOrFail());
    }

    public function testReturnsCostCentersBySalesOrderId(): void
    {
        // Arrange
        $targetCostCenter = $this->tester->haveCostCenter();
        $this->tester->haveCostCenter();

        $idSalesOrder = $this->tester->haveSalesOrderEntity([
            PurchasingControlBusinessTester::FK_COST_CENTER => $targetCostCenter->getIdCostCenterOrFail(),
        ])->getIdSalesOrder();

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdSalesOrder($idSalesOrder),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenter */
        $retrievedCostCenter = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertSame($targetCostCenter->getIdCostCenterOrFail(), $retrievedCostCenter->getIdCostCenterOrFail());
    }

    public function testReturnsCostCentersWithBudgetsExpanded(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            'isActive' => true,
            'startsAt' => date('Y-m-d', strtotime('-1 day')),
            'endsAt' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->setWithBudgets(true)
                    ->setBudgetActiveOnDate(date('Y-m-d')),
            );

        // Act
        $costCenterCollectionTransfer = $this->tester->getFacade()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionTransfer->getCostCenters());

        /** @var \Generated\Shared\Transfer\CostCenterTransfer $retrievedCostCenterTransfer */
        $retrievedCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();
        $this->assertCount(1, $retrievedCostCenterTransfer->getBudgets());
    }
}

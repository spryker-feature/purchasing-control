<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig as SharedPurchasingControlConfig;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group Facade
 * @group GetBudgetCollectionTest
 * Add your own group annotations below this line
 */
class GetBudgetCollectionTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testReturnsAllBudgetsWithoutConditions(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = new BudgetCriteriaTransfer();

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(3, $budgetCollectionTransfer->getBudgets());
        $this->assertNull($budgetCollectionTransfer->getPagination());
    }

    public function testReturnsBudgetsByCostCenterId(): void
    {
        // Arrange
        $targetCostCenter = $this->tester->haveCostCenter();
        $otherCostCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($targetCostCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($otherCostCenter->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdCostCenter($targetCostCenter->getIdCostCenterOrFail()),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($targetCostCenter->getIdCostCenterOrFail(), $retrievedBudget->getIdCostCenter());
    }

    public function testReturnsBudgetsByMultipleCostCenterIds(): void
    {
        // Arrange
        $costCenter1 = $this->tester->haveCostCenter();
        $costCenter2 = $this->tester->haveCostCenter();
        $costCenter3 = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter1->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter2->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter3->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addIdCostCenter($costCenter1->getIdCostCenterOrFail())
                    ->addIdCostCenter($costCenter2->getIdCostCenterOrFail()),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(2, $budgetCollectionTransfer->getBudgets());
    }

    public function testReturnsBudgetsByIsActiveTrue(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::IS_ACTIVE => true]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::IS_ACTIVE => true]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::IS_ACTIVE => false]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setIsActive(true),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(2, $budgetCollectionTransfer->getBudgets());

        foreach ($budgetCollectionTransfer->getBudgets() as $budget) {
            $this->assertTrue($budget->getIsActive());
        }
    }

    public function testReturnsBudgetsByIsActiveFalse(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::IS_ACTIVE => true]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::IS_ACTIVE => false]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setIsActive(false),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertFalse($retrievedBudget->getIsActive());
    }

    public function testReturnsBudgetsByCurrencyIsoCode(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => 'EUR']);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => 'USD']);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addCurrencyIsoCode('EUR'),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame('EUR', $retrievedBudget->getCurrencyIsoCode());
    }

    public function testReturnsBudgetsActiveOnDate(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => '2024-01-01',
            'endsAt' => '2024-12-31',
        ]);

        // Budget active today
        $activeBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => date('Y-m-d', strtotime('-10 days')),
            'endsAt' => date('Y-m-d', strtotime('+10 days')),
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setActiveOnDate(date('Y-m-d')),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($activeBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testFiltersConditionsWithAndLogic(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => true,
            BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
        ]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => false,
            BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
        ]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => true,
            BudgetTransfer::CURRENCY_ISO_CODE => 'USD',
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->setIsActive(true)
                    ->addCurrencyIsoCode('EUR'),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertTrue($retrievedBudget->getIsActive());
        $this->assertSame('EUR', $retrievedBudget->getCurrencyIsoCode());
    }

    public function testReturnsEmptyCollectionWhenNoBudgetsMatch(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdCostCenter(PHP_INT_MAX),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(0, $budgetCollectionTransfer->getBudgets());
    }

    public function testReturnsBudgetsByLimitAndOffset(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $paginationTransfer = (new PaginationTransfer())
            ->setOffset(1)
            ->setLimit(2);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setPagination($paginationTransfer);

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(2, $budgetCollectionTransfer->getBudgets());
        $this->assertNotNull($budgetCollectionTransfer->getPagination());
        $this->assertSame(4, $budgetCollectionTransfer->getPaginationOrFail()->getNbResultsOrFail());
    }

    public function testReturnsBudgetsByPageAndMaxPerPage(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        for ($i = 0; $i < 7; $i++) {
            $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        }

        $paginationTransfer = (new PaginationTransfer())
            ->setPage(2)
            ->setMaxPerPage(2);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setPagination($paginationTransfer);

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(2, $budgetCollectionTransfer->getBudgets());
        $this->assertNotNull($budgetCollectionTransfer->getPagination());

        $paginationTransfer = $budgetCollectionTransfer->getPaginationOrFail();

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

    public function testReturnsBudgetsSortedByNameAscending(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Gamma']);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Alpha']);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Beta']);

        $sortTransfer = (new SortTransfer())
            ->setField(BudgetTransfer::NAME)
            ->setIsAscending(true);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->addSort($sortTransfer);

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        $budgets = $budgetCollectionTransfer->getBudgets();

        // Assert
        $this->assertCount(3, $budgets);
        $this->assertSame('Alpha', $budgets->offsetGet(0)->getNameOrFail());
        $this->assertSame('Beta', $budgets->offsetGet(1)->getNameOrFail());
        $this->assertSame('Gamma', $budgets->offsetGet(2)->getNameOrFail());
    }

    public function testReturnsBudgetsSortedByNameDescending(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Alpha']);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Beta']);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [BudgetTransfer::NAME => 'Gamma']);

        $sortTransfer = (new SortTransfer())
            ->setField(BudgetTransfer::NAME)
            ->setIsAscending(false);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->addSort($sortTransfer);

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        $budgets = $budgetCollectionTransfer->getBudgets();

        // Assert
        $this->assertCount(3, $budgets);
        $this->assertSame('Gamma', $budgets->offsetGet(0)->getNameOrFail());
        $this->assertSame('Beta', $budgets->offsetGet(1)->getNameOrFail());
        $this->assertSame('Alpha', $budgets->offsetGet(2)->getNameOrFail());
    }

    public function testReturnsBudgetsByUuid(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $targetBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addUuid($targetBudget->getUuidOrFail()),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($targetBudget->getUuidOrFail(), $retrievedBudget->getUuidOrFail());
    }

    public function testReturnsBudgetsByStartsAtFrom(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::STARTS_AT => '2024-01-01',
            BudgetTransfer::ENDS_AT => '2024-12-31',
        ]);

        $futureBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::STARTS_AT => '2026-06-01',
            BudgetTransfer::ENDS_AT => '2026-12-31',
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setStartsAtFrom('2026-01-01'),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($futureBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testReturnsBudgetsByEndsAtTo(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();

        $pastBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::STARTS_AT => '2024-01-01',
            BudgetTransfer::ENDS_AT => '2024-06-30',
        ]);

        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::STARTS_AT => '2026-01-01',
            BudgetTransfer::ENDS_AT => '2026-12-31',
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setEndsAtTo('2025-01-01'),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($pastBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testReturnsBudgetsByBudgetIds(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $targetBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdBudget($targetBudget->getIdBudgetOrFail()),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($targetBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testReturnsBudgetsByNames(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $namedBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::NAME => 'Marketing Q1',
        ]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::NAME => 'Operations Q2',
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addName('Marketing Q1'),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($namedBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testReturnsBudgetsByEnforcementRules(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $blockBudget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::ENFORCEMENT_RULE => SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
        ]);
        $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::ENFORCEMENT_RULE => SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN,
        ]);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->addEnforcementRule(SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame($blockBudget->getIdBudgetOrFail(), $retrievedBudget->getIdBudgetOrFail());
    }

    public function testReturnsConsumedAndRemainingAmountsWhenWithBudgetConsumptionIsTrue(): void
    {
        // Arrange
        $costCenter = $this->tester->haveCostCenter();
        $budget = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);

        $salesOrderId = $this->tester->haveSalesOrderId();
        $this->tester->haveBudgetConsumption($budget->getIdBudgetOrFail(), $salesOrderId, 3000);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())
                    ->addIdBudget($budget->getIdBudgetOrFail())
                    ->setWithBudgetConsumption(true),
            );

        // Act
        $budgetCollectionTransfer = $this->tester->getFacade()
            ->getBudgetCollection($budgetCriteriaTransfer);

        // Assert
        $this->assertCount(1, $budgetCollectionTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $retrievedBudget */
        $retrievedBudget = $budgetCollectionTransfer->getBudgets()->getIterator()->current();
        $this->assertSame(3000, $retrievedBudget->getConsumedAmount());
        $this->assertSame(7000, $retrievedBudget->getRemainingAmount());
    }
}

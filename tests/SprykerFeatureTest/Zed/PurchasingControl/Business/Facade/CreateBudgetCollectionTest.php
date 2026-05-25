<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group Facade
 * @group CreateBudgetCollectionTest
 * Add your own group annotations below this line
 */
class CreateBudgetCollectionTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID
     */
    protected const string GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID = 'purchasing_control.budget.validation.date_range_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID
     */
    protected const string GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID = 'purchasing_control.budget.validation.amount_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID
     */
    protected const string GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID = 'purchasing_control.budget.validation.currency_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_ACCESS_DENIED
     */
    protected const string GLOSSARY_KEY_VALIDATION_ACCESS_DENIED = 'purchasing_control.budget.validation.access_denied';

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testCreatesValidBudget(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail());

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $budgetCollectionResponseTransfer->getErrors());
        $this->assertCount(1, $budgetCollectionResponseTransfer->getBudgets());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $createdBudget */
        $createdBudget = $budgetCollectionResponseTransfer->getBudgets()->getIterator()->current();
        $this->assertNotNull($createdBudget->getIdBudget());
        $this->assertSame($budgetTransfer->getAmount(), $createdBudget->getAmount());
    }

    public function testReturnsErrorForInvalidDateRange(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::STARTS_AT => '2025-12-31',
            BudgetTransfer::ENDS_AT => '2025-01-01',
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID, $messages);
    }

    public function testReturnsErrorForZeroAmount(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 0,
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID, $messages);
    }

    public function testReturnsErrorForInvalidCurrencyCode(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::CURRENCY_ISO_CODE => 'INVALID',
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID, $messages);
    }

    public function testReturnsAccessDeniedErrorWhenCostCenterBelongsToAnotherCompany(): void
    {
        // Arrange
        $ownerCompanyTransfer = $this->tester->haveCompany();
        $ownerBusinessUnit = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $ownerCompanyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $ownerCompanyTransfer,
        ]);
        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $ownerBusinessUnit,
        ]);

        $otherCompanyTransfer = $this->tester->haveCompany();
        $otherBusinessUnit = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $otherCompanyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $otherCompanyTransfer,
        ]);
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail());

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($otherBusinessUnit->getFkCompanyOrFail()),
                ),
            );

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_ACCESS_DENIED, $messages);
    }

    public function testSkipsOwnershipValidationWhenCustomerIsNotProvided(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail());

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $budgetCollectionResponseTransfer->getErrors());
        $this->assertCount(1, $budgetCollectionResponseTransfer->getBudgets());
    }

    public function testReturnsErrorForNegativeAmount(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => -100,
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID, $messages);
    }

    public function testTransactionalModeDoesNotPersistValidBudgetsWhenOneIsInvalid(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $validBudgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail());
        $invalidBudgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 0,
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($validBudgetTransfer)
            ->addBudget($invalidBudgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert — error returned, valid budget was NOT persisted (no idBudget assigned)
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        foreach ($budgetCollectionResponseTransfer->getBudgets() as $budget) {
            $this->assertNull($budget->getIdBudget());
        }
    }

    public function testNonTransactionalModePersistsValidBudgetWhenAnotherIsInvalid(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $validBudgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail());
        $invalidBudgetTransfer = $this->tester->buildBudgetTransfer($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 0,
        ]);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($validBudgetTransfer)
            ->addBudget($invalidBudgetTransfer)
            ->setIsTransactional(false);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->createBudgetCollection($budgetCollectionRequestTransfer);

        // Assert — error for invalid budget, valid budget IS persisted
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());
        $this->assertCount(2, $budgetCollectionResponseTransfer->getBudgets());

        $budgets = $budgetCollectionResponseTransfer->getBudgets()->getArrayCopy();
        $persistedCount = count(array_filter($budgets, static fn ($b) => $b->getIdBudget() !== null));
        $this->assertSame(1, $persistedCount);
    }
}

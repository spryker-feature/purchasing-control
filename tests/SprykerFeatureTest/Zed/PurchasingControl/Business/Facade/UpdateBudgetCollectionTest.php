<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
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
 * @group UpdateBudgetCollectionTest
 * Add your own group annotations below this line
 */
class UpdateBudgetCollectionTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_ACCESS_DENIED
     */
    protected const string GLOSSARY_KEY_VALIDATION_ACCESS_DENIED = 'purchasing_control.budget.validation.access_denied';

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

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testUpdatesExistingBudgetAmount(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);

        $updatedAmount = 20000;
        $budgetTransfer->setAmount($updatedAmount);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $budgetCollectionResponseTransfer->getErrors());

        $persistedCollection = $this->tester->getFacade()->getBudgetCollection(new BudgetCriteriaTransfer());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $persistedBudget */
        $persistedBudget = $persistedCollection->getBudgets()->getIterator()->current();
        $this->assertSame($updatedAmount, $persistedBudget->getAmount());
    }

    public function testDeactivateBudgetViaUpdate(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => true,
        ]);

        $budgetTransfer->setIsActive(false);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $this->tester->getFacade()->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $persistedCollection = $this->tester->getFacade()->getBudgetCollection(new BudgetCriteriaTransfer());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $persistedBudget */
        $persistedBudget = $persistedCollection->getBudgets()->getIterator()->current();
        $this->assertFalse($persistedBudget->getIsActive());
    }

    public function testReactivateBudgetViaUpdate(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => false,
        ]);

        $budgetTransfer->setIsActive(true);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $this->tester->getFacade()->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $persistedCollection = $this->tester->getFacade()->getBudgetCollection(new BudgetCriteriaTransfer());

        /** @var \Generated\Shared\Transfer\BudgetTransfer $persistedBudget */
        $persistedBudget = $persistedCollection->getBudgets()->getIterator()->current();
        $this->assertTrue($persistedBudget->getIsActive());
    }

    public function testReturnsAccessDeniedErrorWhenUpdatingBudgetOfAnotherCompany(): void
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
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);

        $otherCompanyTransfer = $this->tester->haveCompany();
        $otherBusinessUnit = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $otherCompanyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $otherCompanyTransfer,
        ]);
        $budgetTransfer->setAmount(20000);

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
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

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
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);
        $budgetTransfer->setAmount(20000);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $budgetCollectionResponseTransfer->getErrors());
    }

    public function testReturnsErrorForInvalidDateRangeOnUpdate(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $budgetTransfer
            ->setStartsAt('2025-12-31')
            ->setEndsAt('2025-01-01');

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID, $messages);
    }

    public function testReturnsErrorForZeroAmountOnUpdate(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $budgetTransfer->setAmount(0);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID, $messages);
    }

    public function testReturnsErrorForInvalidCurrencyCodeOnUpdate(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail());
        $budgetTransfer->setCurrencyIsoCode('INVALID');

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($budgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $messages = array_map(
            static fn ($error) => $error->getMessage(),
            $budgetCollectionResponseTransfer->getErrors()->getArrayCopy(),
        );
        $this->assertContains(static::GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID, $messages);
    }

    public function testTransactionalModeDoesNotPersistValidBudgetsWhenOneIsInvalid(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $validBudgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);
        $invalidBudgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);

        $validBudgetTransfer->setAmount(20000);
        $invalidBudgetTransfer->setAmount(0);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($validBudgetTransfer)
            ->addBudget($invalidBudgetTransfer)
            ->setIsTransactional(true);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert — error returned, valid budget was NOT updated (amount stays at 10000)
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());

        $persistedCollection = $this->tester->getFacade()->getBudgetCollection(new BudgetCriteriaTransfer());
        foreach ($persistedCollection->getBudgets() as $persistedBudget) {
            $this->assertSame(10000, $persistedBudget->getAmount());
        }
    }

    public function testNonTransactionalModePersistsValidBudgetWhenAnotherIsInvalid(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $validBudgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);
        $invalidBudgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::AMOUNT => 10000,
        ]);

        $validBudgetTransfer->setAmount(20000);
        $invalidBudgetTransfer->setAmount(0);

        $budgetCollectionRequestTransfer = (new BudgetCollectionRequestTransfer())
            ->addBudget($validBudgetTransfer)
            ->addBudget($invalidBudgetTransfer)
            ->setIsTransactional(false);

        // Act
        $budgetCollectionResponseTransfer = $this->tester->getFacade()
            ->updateBudgetCollection($budgetCollectionRequestTransfer);

        // Assert — error for invalid budget, valid budget IS updated to new amount
        $this->assertNotEmpty($budgetCollectionResponseTransfer->getErrors());
        $this->assertCount(2, $budgetCollectionResponseTransfer->getBudgets());

        $persistedCollection = $this->tester->getFacade()->getBudgetCollection(new BudgetCriteriaTransfer());
        $amounts = array_map(
            static fn ($b) => $b->getAmount(),
            $persistedCollection->getBudgets()->getArrayCopy(),
        );
        $this->assertContains(20000, $amounts);
        $this->assertContains(10000, $amounts);
    }
}

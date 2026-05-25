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
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
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
 * @group UpdateCostCenterCollectionTest
 * Add your own group annotations below this line
 */
class UpdateCostCenterCollectionTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED
     */
    protected const string GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED = 'purchasing_control.cost_center.validation.access_denied';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_BU_NOT_IN_COMPANY
     */
    protected const string GLOSSARY_KEY_BU_NOT_IN_COMPANY = 'purchasing_control.cost_center.validation.business_unit_not_in_company';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_VALIDATION_NAME_EMPTY
     */
    protected const string GLOSSARY_KEY_VALIDATION_NAME_EMPTY = 'purchasing_control.cost_center.validation.name_empty';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY
     */
    protected const string GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY = 'purchasing_control.cost_center.validation.business_unit_empty';

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testUpdateSucceedsForOwnCostCenter(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('Updated Name')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransfer->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionResponseTransfer->getErrors());
    }

    public function testUpdateFailsWhenCostCenterBelongsToAnotherCompany(): void
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

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitOfCompanyB,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('Hijacked Name')
            ->addIdCompanyBusinessUnit($companyBusinessUnitOfCompanyB->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyBusinessUnitOfCompanyA->getFkCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testUpdateFailsWhenBusinessUnitBelongsToAnotherCompany(): void
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

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitOfCompanyA,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('Updated Name')
            ->addIdCompanyBusinessUnit($companyBusinessUnitOfCompanyB->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransferA->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_BU_NOT_IN_COMPANY,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testDeactivatingCostCenterAlsoDeactivatesItsActiveBudgets(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
        ]);
        $idCostCenter = $existingCostCenterTransfer->getIdCostCenterOrFail();

        $this->tester->haveBudget($idCostCenter, ['isActive' => true]);
        $this->tester->haveBudget($idCostCenter, ['isActive' => true]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setName($existingCostCenterTransfer->getNameOrFail())
            ->setIsActive(false)
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer);

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionResponseTransfer->getErrors());

        $budgetCollectionTransfer = $this->tester->getFacade()->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdCostCenter($idCostCenter),
            ),
        );

        foreach ($budgetCollectionTransfer->getBudgets() as $budgetTransfer) {
            $this->assertFalse($budgetTransfer->getIsActive());
        }
    }

    public function testUpdateSucceedsWithoutCustomerContext(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('Updated via Import')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer);

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionResponseTransfer->getErrors());
    }

    public function testUpdateFailsWhenNameIsEmpty(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransfer->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_VALIDATION_NAME_EMPTY,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testUpdateFailsWhenNoBusinessUnitsProvided(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setName('Updated Name');

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransfer->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testTransactionalModeDoesNotPersistValidCostCentersWhenOneIsInvalid(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenter1 = $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);
        $existingCostCenter2 = $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);

        $validCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenter1->getIdCostCenterOrFail())
            ->setName('Valid Updated Name')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $invalidCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenter2->getIdCostCenterOrFail())
            ->setName('');

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($validCostCenterTransfer)
            ->addCostCenter($invalidCostCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransfer->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert — error returned, valid cost center was NOT updated in DB (name stays at original)
        $this->assertNotEmpty($costCenterCollectionResponseTransfer->getErrors());

        $persistedCollection = $this->tester->getFacade()->getCostCenterCollection(new CostCenterCriteriaTransfer());
        foreach ($persistedCollection->getCostCenters() as $persistedCostCenter) {
            $this->assertNotSame('Valid Updated Name', $persistedCostCenter->getName());
        }
    }

    public function testNonTransactionalModePersistsValidCostCenterWhenAnotherIsInvalid(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $existingCostCenter1 = $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);
        $existingCostCenter2 = $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);

        $validCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenter1->getIdCostCenterOrFail())
            ->setName('Valid Updated Name')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $invalidCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($existingCostCenter2->getIdCostCenterOrFail())
            ->setName('');

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(false)
            ->addCostCenter($validCostCenterTransfer)
            ->addCostCenter($invalidCostCenterTransfer)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompany($companyTransfer->getIdCompanyOrFail()),
                ),
            );

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->updateCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert — error for invalid cost center, valid cost center IS updated
        $this->assertNotEmpty($costCenterCollectionResponseTransfer->getErrors());
        $this->assertCount(2, $costCenterCollectionResponseTransfer->getCostCenters());

        $costCenters = $costCenterCollectionResponseTransfer->getCostCenters()->getArrayCopy();
        $updatedCount = count(array_filter($costCenters, static fn ($cc) => $cc->getName() === 'Valid Updated Name'));
        $this->assertSame(1, $updatedCount);
    }
}

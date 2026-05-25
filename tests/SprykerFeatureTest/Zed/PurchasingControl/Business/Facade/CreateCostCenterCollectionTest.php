<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
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
 * @group CreateCostCenterCollectionTest
 * Add your own group annotations below this line
 */
class CreateCostCenterCollectionTest extends Unit
{
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

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testCreateSucceedsWhenBusinessUnitBelongsToCompany(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setName('Cost Center A')
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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertNotNull(
            $costCenterCollectionResponseTransfer->getCostCenters()->getIterator()->current()->getIdCostCenter(),
        );
    }

    public function testCreateFailsWhenBusinessUnitBelongsToAnotherCompany(): void
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

        $costCenterTransfer = (new CostCenterTransfer())
            ->setName('Cost Center A')
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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_BU_NOT_IN_COMPANY,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testCreateSucceedsWithoutCustomerContext(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
            ->setName('Imported Cost Center')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $costCenterCollectionRequestTransfer = (new CostCenterCollectionRequestTransfer())
            ->setIsTransactional(true)
            ->addCostCenter($costCenterTransfer);

        // Act
        $costCenterCollectionResponseTransfer = $this->tester->getFacade()
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(0, $costCenterCollectionResponseTransfer->getErrors());
    }

    public function testCreateFailsWhenNameIsEmpty(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = (new CostCenterTransfer())
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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $costCenterCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_VALIDATION_NAME_EMPTY,
            $costCenterCollectionResponseTransfer->getErrors()->getIterator()->current()->getMessageOrFail(),
        );
    }

    public function testCreateFailsWhenNoBusinessUnitsProvided(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();

        $costCenterTransfer = (new CostCenterTransfer())->setName('Cost Center A');

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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

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

        $validCostCenterTransfer = (new CostCenterTransfer())
            ->setName('Valid Cost Center')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $invalidCostCenterTransfer = (new CostCenterTransfer())
            ->setName('')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert — error returned, valid cost center was NOT persisted (no idCostCenter assigned)
        $this->assertNotEmpty($costCenterCollectionResponseTransfer->getErrors());

        foreach ($costCenterCollectionResponseTransfer->getCostCenters() as $costCenter) {
            $this->assertNull($costCenter->getIdCostCenter());
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

        $validCostCenterTransfer = (new CostCenterTransfer())
            ->setName('Valid Cost Center')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

        $invalidCostCenterTransfer = (new CostCenterTransfer())
            ->setName('')
            ->addIdCompanyBusinessUnit($companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail());

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
            ->createCostCenterCollection($costCenterCollectionRequestTransfer);

        // Assert — error for invalid cost center, valid cost center IS persisted
        $this->assertNotEmpty($costCenterCollectionResponseTransfer->getErrors());
        $this->assertCount(2, $costCenterCollectionResponseTransfer->getCostCenters());

        $costCenters = $costCenterCollectionResponseTransfer->getCostCenters()->getArrayCopy();
        $persistedCount = count(array_filter($costCenters, static fn ($cc) => $cc->getIdCostCenter() !== null));
        $this->assertSame(1, $persistedCount);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteApprovalTransfer;
use Generated\Shared\Transfer\QuoteErrorTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\QuoteApproval\QuoteApprovalConfig;
use Spryker\Zed\Quote\Business\QuoteFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlDependencyProvider;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group Facade
 * @group UpdateQuoteCostCenterTest
 * Add your own group annotations below this line
 */
class UpdateQuoteCostCenterTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdater::GLOSSARY_KEY_QUOTE_NOT_FOUND
     */
    protected const string GLOSSARY_KEY_QUOTE_NOT_FOUND = 'purchasing_control.error.quote_not_found';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidator::GLOSSARY_KEY_NOT_COMPANY_USER
     */
    protected const string GLOSSARY_KEY_NOT_COMPANY_USER = 'purchasing_control.error.not_company_user';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidator::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED
     */
    protected const string GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED = 'purchasing_control.error.cost_center_access_denied';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidator::GLOSSARY_KEY_BUDGET_ACCESS_DENIED
     */
    protected const string GLOSSARY_KEY_BUDGET_ACCESS_DENIED = 'purchasing_control.error.budget_access_denied';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidator::GLOSSARY_KEY_QUOTE_APPROVED
     */
    protected const string GLOSSARY_KEY_QUOTE_APPROVED = 'purchasing_control.error.quote_approved';

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testReturnsErrorWhenQuoteNotFound(): void
    {
        // Arrange
        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(false));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(PHP_INT_MAX)
            ->setIdCostCenter(1);

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_QUOTE_NOT_FOUND,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testReturnsErrorWhenCustomerIsNotCompanyUser(): void
    {
        // Arrange
        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter(1);

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_NOT_COMPANY_USER,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testReturnsErrorWhenCostCenterDoesNotBelongToBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $otherCompanyTransfer = $this->tester->haveCompany();
        $otherCompanyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $otherCompanyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $otherCompanyTransfer,
        ]);

        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $otherCompanyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testReturnsErrorWhenBudgetDoesNotBelongToCostCenter(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $otherCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $budgetOfOtherCostCenter = $this->tester->haveBudget($otherCostCenterTransfer->getIdCostCenterOrFail());

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget($budgetOfOtherCostCenter->getIdBudgetOrFail())
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_BUDGET_ACCESS_DENIED,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testUpdatesQuoteSuccessfullyWithCostCenterAndBudget(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::IS_ACTIVE => true,
        ]);

        $updatedQuoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail());

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));
        $quoteFacadeMock->method('updateQuote')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer($updatedQuoteTransfer));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(0, $responseTransfer->getErrors());
        $this->assertSame($costCenterTransfer->getIdCostCenterOrFail(), $responseTransfer->getQuoteOrFail()->getIdCostCenter());
        $this->assertSame($budgetTransfer->getIdBudgetOrFail(), $responseTransfer->getQuoteOrFail()->getIdBudget());
    }

    public function testUpdatesQuoteSuccessfullyWithCostCenterAndNoBudget(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);

        $updatedQuoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget(null);

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));
        $quoteFacadeMock->method('updateQuote')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer($updatedQuoteTransfer));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget(null)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertTrue($responseTransfer->getIsSuccessful());
        $this->assertCount(0, $responseTransfer->getErrors());
        $this->assertSame($costCenterTransfer->getIdCostCenterOrFail(), $responseTransfer->getQuoteOrFail()->getIdCostCenter());
        $this->assertNull($responseTransfer->getQuoteOrFail()->getIdBudget());
    }

    public function testReturnsErrorWhenQuoteIsApproved(): void
    {
        // Arrange
        $quoteTransfer = (new QuoteTransfer())
            ->addQuoteApproval((new QuoteApprovalTransfer())->setStatus(QuoteApprovalConfig::STATUS_APPROVED));

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer($quoteTransfer));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter(1);

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            static::GLOSSARY_KEY_QUOTE_APPROVED,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testDoesNotBlockUpdateWhenQuoteApprovalStatusIsNotApproved(): void
    {
        // Arrange
        $quoteTransfer = (new QuoteTransfer())
            ->addQuoteApproval((new QuoteApprovalTransfer())->setStatus('pending'));

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer($quoteTransfer));

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter(1);

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert — approval check passed, proceeds to company user validation
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertSame(
            static::GLOSSARY_KEY_NOT_COMPANY_USER,
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testMapsUpdateQuoteErrorsToResponse(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $costCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);

        $quoteFacadeMock = $this->createMock(QuoteFacadeInterface::class);
        $quoteFacadeMock->method('findQuoteById')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true)->setQuoteTransfer(new QuoteTransfer()));
        $quoteFacadeMock->method('updateQuote')
            ->willReturn(
                (new QuoteResponseTransfer())
                    ->setIsSuccessful(false)
                    ->addError((new QuoteErrorTransfer())->setMessage('quote.validation.error.some_error')),
            );

        $this->tester->setDependency(PurchasingControlDependencyProvider::FACADE_QUOTE, $quoteFacadeMock);

        $requestTransfer = (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote(1)
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget(null)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $responseTransfer = $this->tester->getFacade()->updateQuoteCostCenter($requestTransfer);

        // Assert
        $this->assertFalse($responseTransfer->getIsSuccessful());
        $this->assertCount(1, $responseTransfer->getErrors());
        $this->assertSame(
            'quote.validation.error.some_error',
            $responseTransfer->getErrors()->getIterator()->current()->getMessage(),
        );
    }
}

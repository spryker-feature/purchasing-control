<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Quote\CostCenterQuoteExpanderPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterQuoteExpanderPluginTest
 */
class CostCenterQuoteExpanderPluginTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected const string CURRENCY_EUR = 'EUR';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testExpandReturnsUnchangedQuoteWhenNoCompanyBusinessUnit(): void
    {
        // Arrange
        $quoteTransfer = new QuoteTransfer();

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertNull($result->getIdCostCenter());
        $this->assertNull($result->getIdBudget());
    }

    public function testExpandSetsCostCenterWhenExactlyOneActiveCostCenterMatchesBusinessUnit(): void
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
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::CURRENCY_ISO_CODE => static::CURRENCY_EUR,
            BudgetTransfer::IS_ACTIVE => true,
            BudgetTransfer::STARTS_AT => date('Y-m-d', strtotime('-1 day')),
            BudgetTransfer::ENDS_AT => date('Y-m-d', strtotime('+1 day')),
        ]);

        $quoteTransfer = (new QuoteTransfer())
            ->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_EUR))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertSame($costCenterTransfer->getIdCostCenterOrFail(), $result->getIdCostCenter());
        $this->assertNotNull($result->getIdBudget());
    }

    public function testExpandDoesNotSetCostCenterWhenMultipleCostCentersMatchBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $firstCostCenterTransfer = $this->tester->haveCostCenter([
           static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $this->tester->haveBudget($firstCostCenterTransfer->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => static::CURRENCY_EUR, BudgetTransfer::IS_ACTIVE => true]);

        $secondCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);
        $this->tester->haveBudget($secondCostCenterTransfer->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => static::CURRENCY_EUR, BudgetTransfer::IS_ACTIVE => true]);

        $quoteTransfer = (new QuoteTransfer())
            ->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_EUR))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertNull($result->getIdCostCenter());
        $this->assertNull($result->getIdBudget());
    }

    public function testExpandDoesNotOverrideCostCenterAlreadySetOnQuote(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $existingCostCenterTransfer = $this->tester->haveCostCenter([
            static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer,
            CostCenterTransfer::IS_ACTIVE => true,
        ]);

        $quoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($existingCostCenterTransfer->getIdCostCenterOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_EUR))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertSame($existingCostCenterTransfer->getIdCostCenterOrFail(), $result->getIdCostCenter());
    }

    public function testExpandDoesNotOverrideBudgetAlreadySetOnQuote(): void
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
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => static::CURRENCY_EUR, BudgetTransfer::IS_ACTIVE => true]);

        $quoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_EUR))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertSame($budgetTransfer->getIdBudgetOrFail(), $result->getIdBudget());
    }

    public function testExpandSetsCostCenterButNotBudgetWhenNoActiveBudgetExists(): void
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
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [BudgetTransfer::CURRENCY_ISO_CODE => static::CURRENCY_EUR, BudgetTransfer::IS_ACTIVE => false]);

        $quoteTransfer = (new QuoteTransfer())
            ->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_EUR))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit(
                        $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                    ),
                ),
            );

        // Act
        $result = (new CostCenterQuoteExpanderPlugin())->expand($quoteTransfer);

        // Assert
        $this->assertSame($costCenterTransfer->getIdCostCenterOrFail(), $result->getIdCostCenter());
        $this->assertNull($result->getIdBudget());
    }
}

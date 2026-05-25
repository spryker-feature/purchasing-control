<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Checkout\BudgetCheckoutPreConditionPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group BudgetCheckoutPreConditionPluginTest
 */
class BudgetCheckoutPreConditionPluginTest extends Unit
{
    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testCheckConditionReturnsTrueWhenNoBudgetAndNoCustomerContext(): void
    {
        // Arrange
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            new QuoteTransfer(),
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsTrueWhenNoBudgetAndBusinessUnitHasNoActiveCostCenters(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $businessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $this->tester->haveCostCenter([
            'companyBusinessUnit' => $businessUnitTransfer,
        ]); // cost center without any budget → won't appear in EUR query

        $quoteTransfer = (new QuoteTransfer())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($businessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
                ),
            );

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsFalseWhenNoBudgetAndBusinessUnitHasActiveCostCenter(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $businessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $costCenterTransfer = $this->tester->haveCostCenter([
            'companyBusinessUnit' => $businessUnitTransfer,
            'isActive' => true,
        ]);
        $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
            BudgetTransfer::IS_ACTIVE => true,
        ]);

        $quoteTransfer = (new QuoteTransfer())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($businessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
                ),
            );

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertFalse($result);
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsFalseWhenBudgetCostCenterDoesNotBelongToCustomerBusinessUnit(): void
    {
        // Arrange
        $companyTransfer = $this->tester->haveCompany();
        $businessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);
        $customerCostCenter = $this->tester->haveCostCenter([
            'companyBusinessUnit' => $businessUnitTransfer,
            'isActive' => true,
        ]);
        $this->tester->haveBudget($customerCostCenter->getIdCostCenterOrFail(), ['currencyIsoCode' => 'EUR']);

        $otherCostCenter = $this->tester->haveCostCenter(['isActive' => true]); // linked to a different BU
        $otherBudget = $this->tester->haveBudget($otherCostCenter->getIdCostCenterOrFail(), ['currencyIsoCode' => 'EUR']);

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($otherBudget->getIdBudgetOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($businessUnitTransfer->getIdCompanyBusinessUnitOrFail()),
                ),
            )
            ->setTotals((new TotalsTransfer())->setGrandTotal(500));

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertFalse($result);
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsTrueWhenBudgetHasSufficientBalance(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), ['amount' => 10000]);

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setTotals((new TotalsTransfer())->setGrandTotal(5000));

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    /**
     * @group ddd
     */
    public function testCheckConditionReturnsFalseAndAddsErrorWhenBudgetInsufficientWithBlockRule(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter(['isActive' => true]);
        $budgetTransfer = $this->tester->haveBudget(
            $costCenterTransfer->getIdCostCenterOrFail(),
            [
                'amount' => 1000,
                'enforcementRule' => 'block',
                BudgetTransfer::IS_ACTIVE => true,
                BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
            ],
        );

        $idCompanyBusinessUnit = $costCenterTransfer->getCompanyBusinessUnitIds()[0];

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($idCompanyBusinessUnit),
                ),
            )
            ->setTotals((new TotalsTransfer())->setGrandTotal(5000));

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertFalse($result);
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsTrueAndAddsWarningWhenBudgetInsufficientWithWarnRule(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter(['isActive' => true]);
        $budgetTransfer = $this->tester->haveBudget(
            $costCenterTransfer->getIdCostCenterOrFail(),
            [
                'amount' => 1000,
                'enforcementRule' => 'warn',
                BudgetTransfer::IS_ACTIVE => true,
                BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
            ],
        );

        $idCompanyBusinessUnit = $costCenterTransfer->getCompanyBusinessUnitIds()[0];

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($idCompanyBusinessUnit),
                ),
            )
            ->setTotals((new TotalsTransfer())->setGrandTotal(5000));

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertTrue($result);
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
    }

    public function testCheckConditionReturnsFalseWithRequireApprovalErrorWhenQuoteNotInApprovalProcess(): void
    {
        // Arrange
        $costCenterTransfer = $this->tester->haveCostCenter(['isActive' => true]);
        $budgetTransfer = $this->tester->haveBudget(
            $costCenterTransfer->getIdCostCenterOrFail(),
            [
                'amount' => 1000,
                'enforcementRule' => 'require_approval',
                BudgetTransfer::IS_ACTIVE => true,
                BudgetTransfer::CURRENCY_ISO_CODE => 'EUR',
            ],
        );

        $idCompanyBusinessUnit = $costCenterTransfer->getCompanyBusinessUnitIds()[0];

        $quoteTransfer = (new QuoteTransfer())
            ->setIdBudget($budgetTransfer->getIdBudgetOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode('EUR'))
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer(
                    (new CompanyUserTransfer())->setFkCompanyBusinessUnit($idCompanyBusinessUnit),
                ),
            )
            ->setTotals((new TotalsTransfer())->setGrandTotal(5000));

        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = (new BudgetCheckoutPreConditionPlugin())->checkCondition(
            $quoteTransfer,
            $checkoutResponseTransfer,
        );

        // Assert
        $this->assertFalse($result);
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
    }
}

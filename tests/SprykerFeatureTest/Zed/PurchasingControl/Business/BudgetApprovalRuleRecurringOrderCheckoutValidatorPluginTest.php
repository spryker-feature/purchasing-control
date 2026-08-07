<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig as SharedPurchasingControlConfig;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\OrderExperienceManagement\BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group BudgetApprovalRuleRecurringOrderCheckoutValidatorPluginTest
 */
class BudgetApprovalRuleRecurringOrderCheckoutValidatorPluginTest extends Unit
{
    protected const string GLOSSARY_KEY_VALIDATION_APPROVAL_RULE_NOT_SUPPORTED = 'purchasing_control.validation.approval-rule-not-supported';

    protected const int BUDGET_AMOUNT = 100000;

    protected const int QUOTE_GRAND_TOTAL = 100;

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testValidateReturnsCheckoutErrorWhenBudgetRequiresApprovalAndGrandTotalFitsIntoBudget(): void
    {
        // Arrange
        $budgetTransfer = $this->haveBudgetWithEnforcementRule(
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL,
        );
        $quoteTransfer = $this->createQuoteTransfer($budgetTransfer->getIdBudgetOrFail());

        // Act
        $checkoutErrorTransfer = (new BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin())->validate($quoteTransfer);

        // Assert
        $this->assertSame(
            static::GLOSSARY_KEY_VALIDATION_APPROVAL_RULE_NOT_SUPPORTED,
            $checkoutErrorTransfer?->getMessage(),
        );
    }

    public function testValidateReturnsNullWhenBudgetEnforcementRuleIsBlock(): void
    {
        // Arrange
        $budgetTransfer = $this->haveBudgetWithEnforcementRule(
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
        );
        $quoteTransfer = $this->createQuoteTransfer($budgetTransfer->getIdBudgetOrFail());

        // Act
        $checkoutErrorTransfer = (new BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin())->validate($quoteTransfer);

        // Assert
        $this->assertNull($checkoutErrorTransfer);
    }

    public function testValidateReturnsNullWhenBudgetEnforcementRuleIsWarn(): void
    {
        // Arrange
        $budgetTransfer = $this->haveBudgetWithEnforcementRule(
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN,
        );
        $quoteTransfer = $this->createQuoteTransfer($budgetTransfer->getIdBudgetOrFail());

        // Act
        $checkoutErrorTransfer = (new BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin())->validate($quoteTransfer);

        // Assert
        $this->assertNull($checkoutErrorTransfer);
    }

    public function testValidateReturnsNullWhenQuoteHasNoBudgetSelected(): void
    {
        // Arrange
        $quoteTransfer = $this->createQuoteTransfer(null);

        // Act
        $checkoutErrorTransfer = (new BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin())->validate($quoteTransfer);

        // Assert
        $this->assertNull($checkoutErrorTransfer);
    }

    public function testValidateReturnsNullWhenBudgetDoesNotExist(): void
    {
        // Arrange
        $budgetTransfer = $this->haveBudgetWithEnforcementRule(
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL,
        );
        $quoteTransfer = $this->createQuoteTransfer($budgetTransfer->getIdBudgetOrFail() + 1);

        // Act
        $checkoutErrorTransfer = (new BudgetApprovalRuleRecurringOrderCheckoutValidatorPlugin())->validate($quoteTransfer);

        // Assert
        $this->assertNull($checkoutErrorTransfer);
    }

    protected function haveBudgetWithEnforcementRule(string $enforcementRule): BudgetTransfer
    {
        $costCenterTransfer = $this->tester->haveCostCenter();

        return $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::ENFORCEMENT_RULE => $enforcementRule,
            BudgetTransfer::AMOUNT => static::BUDGET_AMOUNT,
            BudgetTransfer::IS_ACTIVE => true,
        ]);
    }

    protected function createQuoteTransfer(?int $idBudget): QuoteTransfer
    {
        return (new QuoteTransfer())
            ->setIdBudget($idBudget)
            ->setTotals((new TotalsTransfer())->setGrandTotal(static::QUOTE_GRAND_TOTAL));
    }
}

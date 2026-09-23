<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\OrderIntakeRequestTransfer;
use Generated\Shared\Transfer\OrderIntakeResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\OrderExperienceManagement\BudgetOrderIntakeQuoteExpanderPlugin;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group BudgetOrderIntakeQuoteExpanderPluginTest
 */
class BudgetOrderIntakeQuoteExpanderPluginTest extends Unit
{
    protected const string BUDGET_UUID = '3fa85f64-5717-4562-b3fc-2c963f66afa6';

    protected const string UNKNOWN_BUDGET_UUID = 'unknown-uuid';

    protected const string FIELD_BUDGET_UUID = 'budgetUuid';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    /**
     * The cost center is not incidental: `CostCenterOrderSaver::saveCostCenterToOrder()` returns
     * early when `idCostCenter` is null, so a quote carrying only the budget would have the amount
     * validated and consumed while `fk_budget` never reached the order — an order that spends a
     * budget without recording which.
     */
    public function testExpandQuoteSetsTheBudgetAndItsCostCenterOnTheQuoteWhenTheUuidResolves(): void
    {
        // Arrange
        $budgetTransfer = $this->haveBudgetWithUuid();
        $orderIntakeRequestTransfer = (new OrderIntakeRequestTransfer())->setBudgetUuid($budgetTransfer->getUuidOrFail());
        $orderIntakeResponseTransfer = new OrderIntakeResponseTransfer();

        // Act
        $quoteTransfer = (new BudgetOrderIntakeQuoteExpanderPlugin())
            ->expandQuote($orderIntakeRequestTransfer, new QuoteTransfer(), $orderIntakeResponseTransfer);

        // Assert
        $this->assertSame($budgetTransfer->getIdBudget(), $quoteTransfer->getIdBudget());
        $this->assertSame($budgetTransfer->getIdCostCenter(), $quoteTransfer->getIdCostCenter());
        $this->assertCount(0, $orderIntakeResponseTransfer->getValidationIssues());
    }

    /**
     * Ignoring an unresolvable uuid would place the order against no budget at all, which succeeds —
     * and leaves the spend invisible to whoever set the budget up.
     */
    public function testExpandQuoteReportsAUuidThatResolvesToNoBudget(): void
    {
        // Arrange
        $orderIntakeRequestTransfer = (new OrderIntakeRequestTransfer())->setBudgetUuid(static::UNKNOWN_BUDGET_UUID);
        $orderIntakeResponseTransfer = new OrderIntakeResponseTransfer();

        // Act
        $quoteTransfer = (new BudgetOrderIntakeQuoteExpanderPlugin())
            ->expandQuote($orderIntakeRequestTransfer, new QuoteTransfer(), $orderIntakeResponseTransfer);

        // Assert
        $validationIssueTransfer = $orderIntakeResponseTransfer->getValidationIssues()->offsetGet(0);

        $this->assertCount(1, $orderIntakeResponseTransfer->getValidationIssues());
        $this->assertSame(static::FIELD_BUDGET_UUID, $validationIssueTransfer->getField());
        $this->assertStringContainsString(static::UNKNOWN_BUDGET_UUID, (string)$validationIssueTransfer->getMessage());
        $this->assertNull($quoteTransfer->getIdBudget());
    }

    /**
     * Most orders name no budget: the plugin must hand the quote straight back rather than report
     * every such order as a budget problem.
     */
    public function testExpandQuoteReturnsTheQuoteUntouchedWhenTheRequestNamesNoBudget(): void
    {
        // Arrange
        $inputQuoteTransfer = new QuoteTransfer();
        $orderIntakeResponseTransfer = new OrderIntakeResponseTransfer();

        // Act
        $quoteTransfer = (new BudgetOrderIntakeQuoteExpanderPlugin())
            ->expandQuote(new OrderIntakeRequestTransfer(), $inputQuoteTransfer, $orderIntakeResponseTransfer);

        // Assert
        $this->assertSame($inputQuoteTransfer, $quoteTransfer);
        $this->assertNull($quoteTransfer->getIdBudget());
        $this->assertCount(0, $orderIntakeResponseTransfer->getValidationIssues());
    }

    protected function haveBudgetWithUuid(): BudgetTransfer
    {
        $costCenterTransfer = $this->tester->haveCostCenter();

        return $this->tester->haveBudget($costCenterTransfer->getIdCostCenterOrFail(), [
            BudgetTransfer::UUID => static::BUDGET_UUID,
        ]);
    }
}

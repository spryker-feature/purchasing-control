<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Form\DataProvider;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Money\MoneyClientInterface;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterSelectorFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Resolver\BudgetResolverInterface;
use SprykerFeature\Yves\PurchasingControl\Resolver\CostCenterResolverInterface;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Form
 * @group DataProvider
 * @group CostCenterSelectorFormDataProviderTest
 */
class CostCenterSelectorFormDataProviderTest extends Unit
{
    protected const int ID_COMPANY_BUSINESS_UNIT = 1;

    protected const int ID_COST_CENTER_FIRST = 10;

    protected const int ID_COST_CENTER_SECOND = 20;

    protected const int ID_BUDGET_FIRST = 100;

    protected const int ID_BUDGET_SECOND = 200;

    protected const int ID_BUDGET_THIRD = 300;

    protected const string NAME_COST_CENTER_FIRST = 'Marketing';

    protected const string NAME_COST_CENTER_SECOND = 'Engineering';

    protected const string NAME_BUDGET_SHARED = 'Q1';

    protected const string NAME_BUDGET_THIRD = 'Q2';

    protected const int REMAINING_AMOUNT_FIRST = 1000;

    protected const int REMAINING_AMOUNT_SECOND = 2000;

    protected const int REMAINING_AMOUNT_THIRD = 3000;

    protected const string CURRENCY_CODE = 'EUR';

    public function testGetDataAndOptionsBuildsCostCenterChoicesKeyedByName(): void
    {
        // Act
        $dataAndOptions = $this->createDataProvider()->getDataAndOptions($this->createQuote());

        // Assert
        $this->assertSame(
            [
                static::NAME_COST_CENTER_FIRST => static::ID_COST_CENTER_FIRST,
                static::NAME_COST_CENTER_SECOND => static::ID_COST_CENTER_SECOND,
            ],
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_OPTIONS][CostCenterSelectorForm::OPTION_COST_CENTER_CHOICES],
        );
    }

    /**
     * The browser side filters the budget select down to the selected cost center, so the options
     * must carry the budgets of every cost center, not only of the selected one.
     */
    public function testGetDataAndOptionsOffersBudgetsOfEveryCostCenter(): void
    {
        // Act
        $options = $this->createDataProvider()->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame(
            [static::ID_BUDGET_FIRST, static::ID_BUDGET_SECOND, static::ID_BUDGET_THIRD],
            $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICES],
        );
    }

    /**
     * CostCenterSelectorForm resolves every choice through the labels array, so a choice without a
     * label would make the label callback fail.
     */
    public function testGetDataAndOptionsKeepsBudgetChoicesAndLabelsInSync(): void
    {
        // Act
        $options = $this->createDataProvider()->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame(
            $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICES],
            array_keys($options[CostCenterSelectorForm::OPTION_BUDGET_LABELS]),
        );
        $this->assertSame(
            $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICES],
            array_keys($options[CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS]),
        );
    }

    /**
     * Two cost centers may hold equally named budgets, which is why labels are keyed by budget id.
     */
    public function testGetDataAndOptionsKeepsEquallyNamedBudgetsOfDifferentCostCenters(): void
    {
        // Act
        $options = $this->createDataProvider()->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame(
            [
                static::ID_BUDGET_FIRST => static::NAME_BUDGET_SHARED . ' (' . static::REMAINING_AMOUNT_FIRST . ' ' . static::CURRENCY_CODE . ')',
                static::ID_BUDGET_SECOND => static::NAME_BUDGET_SHARED . ' (' . static::REMAINING_AMOUNT_SECOND . ' ' . static::CURRENCY_CODE . ')',
                static::ID_BUDGET_THIRD => static::NAME_BUDGET_THIRD . ' (' . static::REMAINING_AMOUNT_THIRD . ' ' . static::CURRENCY_CODE . ')',
            ],
            $options[CostCenterSelectorForm::OPTION_BUDGET_LABELS],
        );
    }

    /**
     * The cost center id attribute is the only thing the browser side filter matches on.
     */
    public function testGetDataAndOptionsExposesTheOwningCostCenterOnEveryBudgetChoice(): void
    {
        // Act
        $options = $this->createDataProvider()->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame(
            [
                'data-budget-id' => static::ID_BUDGET_THIRD,
                'data-cost-center-id' => static::ID_COST_CENTER_SECOND,
                'data-remaining-amount' => static::REMAINING_AMOUNT_THIRD . ' ' . static::CURRENCY_CODE,
            ],
            $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS][static::ID_BUDGET_THIRD],
        );
    }

    /**
     * CostCenterSelectorForm rejects a mismatching pair on submit using this map.
     */
    public function testGetDataAndOptionsMapsEveryBudgetToItsCostCenter(): void
    {
        // Act
        $options = $this->createDataProvider()->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame(
            [
                static::ID_BUDGET_FIRST => static::ID_COST_CENTER_FIRST,
                static::ID_BUDGET_SECOND => static::ID_COST_CENTER_FIRST,
                static::ID_BUDGET_THIRD => static::ID_COST_CENTER_SECOND,
            ],
            $options[CostCenterSelectorForm::OPTION_BUDGET_COST_CENTER_MAP],
        );
    }

    public function testGetDataAndOptionsFormatsRemainingAmountsInTheCurrencyOfTheQuote(): void
    {
        // Arrange
        $formattedMoneyTransfers = [];

        $moneyClientMock = $this->createMock(MoneyClientInterface::class);
        $moneyClientMock->method('formatWithSymbol')->willReturnCallback(
            static function (MoneyTransfer $moneyTransfer) use (&$formattedMoneyTransfers): string {
                $formattedMoneyTransfers[] = $moneyTransfer->getCurrencyOrFail()->getCodeOrFail()
                    . ':' . $moneyTransfer->getAmountOrFail();

                return '';
            },
        );

        // Act
        $this->createDataProvider($moneyClientMock)->getDataAndOptions($this->createQuote());

        // Assert
        $this->assertSame(
            [
                static::CURRENCY_CODE . ':' . static::REMAINING_AMOUNT_FIRST,
                static::CURRENCY_CODE . ':' . static::REMAINING_AMOUNT_SECOND,
                static::CURRENCY_CODE . ':' . static::REMAINING_AMOUNT_THIRD,
            ],
            $formattedMoneyTransfers,
        );
    }

    public function testGetDataAndOptionsReturnsSelectionOfTheQuote(): void
    {
        // Act
        $dataAndOptions = $this->createDataProvider()->getDataAndOptions($this->createQuote());

        // Assert
        $this->assertSame(
            [
                CostCenterSelectorForm::FIELD_ID_COST_CENTER => static::ID_COST_CENTER_SECOND,
                CostCenterSelectorForm::FIELD_ID_BUDGET => static::ID_BUDGET_THIRD,
            ],
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_DATA],
        );
        $this->assertSame(
            static::ID_COST_CENTER_SECOND,
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_COST_CENTER]->getIdCostCenter(),
        );
        $this->assertSame(
            static::ID_BUDGET_THIRD,
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_BUDGET]->getIdBudget(),
        );
    }

    /**
     * The quote request pages pass the business unit of the quote request owner instead of the one
     * of the logged in user.
     */
    public function testGetDataAndOptionsPassesTheCompanyBusinessUnitToTheCostCenterResolver(): void
    {
        // Arrange
        $quoteTransfer = $this->createQuote();

        $costCenterResolverMock = $this->createMock(CostCenterResolverInterface::class);
        $costCenterResolverMock->expects($this->once())
            ->method('resolveCostCenters')
            ->with($quoteTransfer, static::ID_COMPANY_BUSINESS_UNIT)
            ->willReturn(new ArrayObject());

        // Act
        $dataProvider = new CostCenterSelectorFormDataProvider(
            $costCenterResolverMock,
            $this->createMock(BudgetResolverInterface::class),
            $this->createMoneyClientMock(),
        );
        $dataProvider->getDataAndOptions($quoteTransfer, static::ID_COMPANY_BUSINESS_UNIT);
    }

    public function testGetDataAndOptionsReturnsEmptyOptionsWhenNoCostCenterIsAvailable(): void
    {
        // Arrange
        $costCenterResolverMock = $this->createMock(CostCenterResolverInterface::class);
        $costCenterResolverMock->method('resolveCostCenters')->willReturn(new ArrayObject());

        $moneyClientMock = $this->createMoneyClientMock();
        $moneyClientMock->expects($this->never())->method('formatWithSymbol');

        // Act
        $dataProvider = new CostCenterSelectorFormDataProvider(
            $costCenterResolverMock,
            $this->createMock(BudgetResolverInterface::class),
            $moneyClientMock,
        );
        $options = $dataProvider->getDataAndOptions($this->createQuote())[CostCenterSelectorFormDataProvider::KEY_OPTIONS];

        // Assert
        $this->assertSame([], $options[CostCenterSelectorForm::OPTION_COST_CENTER_CHOICES]);
        $this->assertSame([], $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICES]);
        $this->assertSame([], $options[CostCenterSelectorForm::OPTION_BUDGET_LABELS]);
        $this->assertSame([], $options[CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS]);
        $this->assertSame([], $options[CostCenterSelectorForm::OPTION_BUDGET_COST_CENTER_MAP]);
    }

    protected function createDataProvider(?MoneyClientInterface $moneyClient = null): CostCenterSelectorFormDataProvider
    {
        $costCenterTransfers = $this->createCostCenterTransfers();

        $costCenterResolverMock = $this->createMock(CostCenterResolverInterface::class);
        $costCenterResolverMock->method('resolveCostCenters')->willReturn($costCenterTransfers);
        $costCenterResolverMock->method('resolveSelectedCostCenter')->willReturn($costCenterTransfers[1]);

        $budgetResolverMock = $this->createMock(BudgetResolverInterface::class);
        $budgetResolverMock->method('resolveSelectedBudget')->willReturn($costCenterTransfers[1]->getBudgets()[0]);

        return new CostCenterSelectorFormDataProvider(
            $costCenterResolverMock,
            $budgetResolverMock,
            $moneyClient ?? $this->createMoneyClientMock(),
        );
    }

    /**
     * @return \Spryker\Client\Money\MoneyClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMoneyClientMock(): MoneyClientInterface
    {
        $moneyClientMock = $this->createMock(MoneyClientInterface::class);
        $moneyClientMock->method('formatWithSymbol')->willReturnCallback(
            static function (MoneyTransfer $moneyTransfer): string {
                return $moneyTransfer->getAmountOrFail() . ' ' . $moneyTransfer->getCurrencyOrFail()->getCodeOrFail();
            },
        );

        return $moneyClientMock;
    }

    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer>
     */
    protected function createCostCenterTransfers(): ArrayObject
    {
        $firstCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter(static::ID_COST_CENTER_FIRST)
            ->setName(static::NAME_COST_CENTER_FIRST)
            ->addBudget($this->createBudget(static::ID_BUDGET_FIRST, static::ID_COST_CENTER_FIRST, static::NAME_BUDGET_SHARED, static::REMAINING_AMOUNT_FIRST))
            ->addBudget($this->createBudget(static::ID_BUDGET_SECOND, static::ID_COST_CENTER_FIRST, static::NAME_BUDGET_SHARED, static::REMAINING_AMOUNT_SECOND));

        $secondCostCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter(static::ID_COST_CENTER_SECOND)
            ->setName(static::NAME_COST_CENTER_SECOND)
            ->addBudget($this->createBudget(static::ID_BUDGET_THIRD, static::ID_COST_CENTER_SECOND, static::NAME_BUDGET_THIRD, static::REMAINING_AMOUNT_THIRD));

        return new ArrayObject([$firstCostCenterTransfer, $secondCostCenterTransfer]);
    }

    protected function createBudget(int $idBudget, int $idCostCenter, string $name, int $remainingAmount): BudgetTransfer
    {
        return (new BudgetTransfer())
            ->setIdBudget($idBudget)
            ->setIdCostCenter($idCostCenter)
            ->setName($name)
            ->setRemainingAmount($remainingAmount);
    }

    protected function createQuote(): QuoteTransfer
    {
        return (new QuoteTransfer())->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_CODE));
    }
}

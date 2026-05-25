<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Money\MoneyClientInterface;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use SprykerFeature\Yves\PurchasingControl\Resolver\BudgetResolverInterface;
use SprykerFeature\Yves\PurchasingControl\Resolver\CostCenterResolverInterface;

class CostCenterSelectorFormDataProvider
{
    public const string KEY_DATA = 'data';

    public const string KEY_OPTIONS = 'options';

    public const string KEY_SELECTED_COST_CENTER = 'selectedCostCenter';

    public const string KEY_SELECTED_BUDGET = 'selectedBudget';

    public function __construct(
        protected CostCenterResolverInterface $costCenterResolver,
        protected BudgetResolverInterface $budgetResolver,
        protected MoneyClientInterface $moneyClient,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataAndOptions(QuoteTransfer $quoteTransfer): array
    {
        $costCenterTransfers = $this->costCenterResolver->resolveCostCenters($quoteTransfer);
        $selectedCostCenterTransfer = $this->costCenterResolver->resolveSelectedCostCenter($costCenterTransfers, $quoteTransfer->getIdCostCenter());
        $budgetTransfers = $selectedCostCenterTransfer?->getBudgets() ?? new ArrayObject();
        $selectedBudgetTransfer = $this->budgetResolver->resolveSelectedBudget($budgetTransfers, $quoteTransfer);

        $currencyCode = $quoteTransfer->getCurrencyOrFail()->getCodeOrFail();

        return [
            static::KEY_DATA => $this->buildData($selectedCostCenterTransfer, $selectedBudgetTransfer),
            static::KEY_OPTIONS => $this->buildOptions($costCenterTransfers, $budgetTransfers, $currencyCode),
            static::KEY_SELECTED_COST_CENTER => $selectedCostCenterTransfer,
            static::KEY_SELECTED_BUDGET => $selectedBudgetTransfer,
        ];
    }

    /**
     * @return array<string, int|null>
     */
    protected function buildData(?CostCenterTransfer $selectedCostCenterTransfer, ?BudgetTransfer $selectedBudgetTransfer): array
    {
        return [
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => $selectedCostCenterTransfer?->getIdCostCenter(),
            CostCenterSelectorForm::FIELD_ID_BUDGET => $selectedBudgetTransfer?->getIdBudget(),
        ];
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenterTransfers
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     *
     * @return array<string, mixed>
     */
    protected function buildOptions(ArrayObject $costCenterTransfers, ArrayObject $budgetTransfers, string $currencyCode): array
    {
        return [
            CostCenterSelectorForm::OPTION_COST_CENTER_CHOICES => $this->buildCostCenterChoices($costCenterTransfers),
            CostCenterSelectorForm::OPTION_BUDGET_CHOICES => $this->buildBudgetChoices($budgetTransfers, $currencyCode),
            CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS => $this->buildBudgetChoiceAttrs($budgetTransfers, $currencyCode),
        ];
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenterTransfers
     *
     * @return array<string, int>
     */
    protected function buildCostCenterChoices(ArrayObject $costCenterTransfers): array
    {
        $choices = [];

        foreach ($costCenterTransfers as $costCenterTransfer) {
            $choices[$costCenterTransfer->getNameOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        return $choices;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     *
     * @return array<string, int>
     */
    protected function buildBudgetChoices(ArrayObject $budgetTransfers, string $currencyCode): array
    {
        $choices = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $label = $this->buildBudgetChoiceLabel($budgetTransfer, $currencyCode);
            $choices[$label] = $budgetTransfer->getIdBudgetOrFail();
        }

        return $choices;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     *
     * @return array<int, array<string, int|string>>
     */
    protected function buildBudgetChoiceAttrs(ArrayObject $budgetTransfers, string $currencyCode): array
    {
        $attrs = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $moneyTransfer = (new MoneyTransfer())
                ->setAmount((string)$budgetTransfer->getRemainingAmountOrFail())
                ->setCurrency((new CurrencyTransfer())->setCode($currencyCode));

            $attrs[$budgetTransfer->getIdBudgetOrFail()] = [
                'data-budget-id' => $budgetTransfer->getIdBudgetOrFail(),
                'data-remaining-amount' => $this->moneyClient->formatWithSymbol($moneyTransfer),
            ];
        }

        return $attrs;
    }

    protected function buildBudgetChoiceLabel(BudgetTransfer $budgetTransfer, string $currencyCode): string
    {
        $moneyTransfer = (new MoneyTransfer())
            ->setAmount((string)$budgetTransfer->getRemainingAmountOrFail())
            ->setCurrency((new CurrencyTransfer())->setCode($currencyCode));

        return sprintf('%s (%s)', $budgetTransfer->getNameOrFail(), $this->moneyClient->formatWithSymbol($moneyTransfer));
    }
}

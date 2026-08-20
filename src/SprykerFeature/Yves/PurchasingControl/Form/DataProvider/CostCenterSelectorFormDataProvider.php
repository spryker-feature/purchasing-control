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
    public function getDataAndOptions(QuoteTransfer $quoteTransfer, ?int $idCompanyBusinessUnit = null): array
    {
        $costCenterTransfers = $this->costCenterResolver->resolveCostCenters($quoteTransfer, $idCompanyBusinessUnit);
        $selectedCostCenterTransfer = $this->costCenterResolver->resolveSelectedCostCenter($costCenterTransfers, $quoteTransfer->getIdCostCenter());
        $budgetTransfers = $selectedCostCenterTransfer?->getBudgets() ?? new ArrayObject();
        $selectedBudgetTransfer = $this->budgetResolver->resolveSelectedBudget($budgetTransfers, $quoteTransfer);

        $currencyCode = $quoteTransfer->getCurrencyOrFail()->getCodeOrFail();

        return [
            static::KEY_DATA => $this->buildData($selectedCostCenterTransfer, $selectedBudgetTransfer),
            static::KEY_OPTIONS => $this->getCostCenterBudgetOptions($costCenterTransfers, $currencyCode),
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
     *
     * @return array<string, mixed>
     */
    protected function getCostCenterBudgetOptions(ArrayObject $costCenterTransfers, string $currencyCode): array
    {
        $budgetLabels = [];
        $budgetChoiceAttrs = [];
        $budgetCostCenterMap = [];

        foreach ($costCenterTransfers as $costCenterTransfer) {
            $budgetTransfers = $costCenterTransfer->getBudgets();
            $formattedRemainingAmounts = $this->formatRemainingAmounts($budgetTransfers, $currencyCode);

            $budgetLabels += $this->buildBudgetLabels($budgetTransfers, $formattedRemainingAmounts);
            $budgetChoiceAttrs += $this->buildBudgetChoiceAttrs($budgetTransfers, $formattedRemainingAmounts);
            $budgetCostCenterMap += $this->buildBudgetCostCenterMap($budgetTransfers);
        }

        return [
            CostCenterSelectorForm::OPTION_COST_CENTER_CHOICES => $this->buildCostCenterChoices($costCenterTransfers),
            CostCenterSelectorForm::OPTION_BUDGET_CHOICES => array_keys($budgetLabels),
            CostCenterSelectorForm::OPTION_BUDGET_LABELS => $budgetLabels,
            CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS => $budgetChoiceAttrs,
            CostCenterSelectorForm::OPTION_BUDGET_COST_CENTER_MAP => $budgetCostCenterMap,
        ];
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     *
     * @return array<int, int> Keys are budget ids, values are the ids of the cost centers they belong to.
     */
    protected function buildBudgetCostCenterMap(ArrayObject $budgetTransfers): array
    {
        $budgetCostCenterMap = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $budgetCostCenterMap[$budgetTransfer->getIdBudgetOrFail()] = $budgetTransfer->getIdCostCenterOrFail();
        }

        return $budgetCostCenterMap;
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
     * @return array<int, string> Keys are budget ids.
     */
    protected function formatRemainingAmounts(ArrayObject $budgetTransfers, string $currencyCode): array
    {
        $formattedRemainingAmounts = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $moneyTransfer = (new MoneyTransfer())
                ->setAmount((string)$budgetTransfer->getRemainingAmountOrFail())
                ->setCurrency((new CurrencyTransfer())->setCode($currencyCode));

            $formattedRemainingAmounts[$budgetTransfer->getIdBudgetOrFail()] = $this->moneyClient->formatWithSymbol($moneyTransfer);
        }

        return $formattedRemainingAmounts;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     * @param array<int, string> $formattedRemainingAmounts
     *
     * @return array<int, string> Keys are budget ids.
     */
    protected function buildBudgetLabels(ArrayObject $budgetTransfers, array $formattedRemainingAmounts): array
    {
        // Keyed by id, not by label: two cost centers can hold equally named budgets.
        $budgetLabels = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $idBudget = $budgetTransfer->getIdBudgetOrFail();
            $budgetLabels[$idBudget] = $this->buildBudgetChoiceLabel($budgetTransfer, $formattedRemainingAmounts[$idBudget]);
        }

        return $budgetLabels;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgetTransfers
     * @param array<int, string> $formattedRemainingAmounts
     *
     * @return array<int, array<string, int|string>>
     */
    protected function buildBudgetChoiceAttrs(ArrayObject $budgetTransfers, array $formattedRemainingAmounts): array
    {
        $attrs = [];

        foreach ($budgetTransfers as $budgetTransfer) {
            $idBudget = $budgetTransfer->getIdBudgetOrFail();

            $attrs[$idBudget] = [
                'data-budget-id' => $idBudget,
                'data-cost-center-id' => $budgetTransfer->getIdCostCenterOrFail(),
                'data-remaining-amount' => $formattedRemainingAmounts[$idBudget],
            ];
        }

        return $attrs;
    }

    protected function buildBudgetChoiceLabel(BudgetTransfer $budgetTransfer, string $formattedRemainingAmount): string
    {
        return sprintf('%s (%s)', $budgetTransfer->getNameOrFail(), $formattedRemainingAmount);
    }
}

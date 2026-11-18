<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Builder;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use SprykerFeature\Yves\PurchasingControl\Formatter\BudgetSummaryFormatterInterface;
use SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig;

class CostCenterBudgetChoiceBuilder implements CostCenterBudgetChoiceBuilderInterface
{
    public function __construct(
        protected readonly BudgetSummaryFormatterInterface $budgetSummaryFormatter,
        protected readonly PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function buildCostCenterChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $choices[$costCenterTransfer->getNameOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    public function buildBudgetChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $choices[$budgetTransfer->getNameOrFail()] = $budgetTransfer->getIdBudgetOrFail();
            }
        }

        return $choices;
    }

    /**
     * @return array<int, int>
     */
    public function buildBudgetCostCenterMap(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $map = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $map[$budgetTransfer->getIdBudgetOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
            }
        }

        return $map;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function buildBudgetChoiceAttrs(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
    ): array {
        $attrs = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $budgetSummaryTransfer = $this->budgetSummaryFormatter->formatBudgetSummary($budgetTransfer, $currencyIsoCode);

                $attrs[$budgetTransfer->getIdBudgetOrFail()] = [
                    'data-cost-center-id' => (string)$costCenterTransfer->getIdCostCenterOrFail(),
                    'data-total-amount' => (string)$budgetSummaryTransfer->getTotalAmount(),
                    'data-used-amount' => (string)$budgetSummaryTransfer->getUsedAmount(),
                    'data-total-formatted' => (string)$budgetSummaryTransfer->getTotalFormatted(),
                    'data-used-formatted' => (string)$budgetSummaryTransfer->getUsedFormatted(),
                    'data-remaining-formatted' => (string)$budgetSummaryTransfer->getRemainingFormatted(),
                    'data-usage-formatted' => (string)$budgetSummaryTransfer->getUsageInfo(),
                ];
            }
        }

        return $attrs;
    }

    /**
     * @return array<\Generated\Shared\Transfer\BudgetTransfer>
     */
    protected function getSelectableBudgets(CostCenterTransfer $costCenterTransfer): array
    {
        $selectableEnforcementRules = $this->purchasingControlConfig->getRecurringOrderSelectableBudgetEnforcementRules();
        $budgetTransfers = [];

        foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
            if (!in_array($budgetTransfer->getEnforcementRule(), $selectableEnforcementRules, true)) {
                continue;
            }

            $budgetTransfers[] = $budgetTransfer;
        }

        return $budgetTransfers;
    }
}

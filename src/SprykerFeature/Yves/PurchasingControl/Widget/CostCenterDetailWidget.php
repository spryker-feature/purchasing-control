<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\BudgetSummaryTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterDetailWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTER_NAME = 'costCenterName';

    protected const string PARAMETER_BUDGET_NAME = 'budgetName';

    protected const string PARAMETER_BUDGET_SUMMARY = 'budgetSummary';

    public function __construct(QuoteTransfer $quoteTransfer, bool $withBudgetSummary = false)
    {
        $costCenterTransfer = $this->getFactory()
            ->createCostCenterReader()
            ->findActiveCostCenterWithBudgets($quoteTransfer->getIdCostCenter());

        $this->addParameter(static::PARAMETER_COST_CENTER_NAME, $costCenterTransfer?->getName());
        $this->addParameter(
            static::PARAMETER_BUDGET_NAME,
            $this->getFactory()->createBudgetReader()->resolveBudgetName($costCenterTransfer, $quoteTransfer->getIdBudget()),
        );

        $this->addParameter(
            static::PARAMETER_BUDGET_SUMMARY,
            $withBudgetSummary ? $this->resolveBudgetSummary($quoteTransfer, $costCenterTransfer) : null,
        );
    }

    protected function resolveBudgetSummary(
        QuoteTransfer $quoteTransfer,
        ?CostCenterTransfer $costCenterTransfer,
    ): ?BudgetSummaryTransfer {
        $budgetTransfer = $this->getFactory()
            ->createBudgetReader()
            ->findBudgetInCostCenter($costCenterTransfer, $quoteTransfer->getIdBudget());
        $currencyIsoCode = $quoteTransfer->getCurrency()?->getCode();

        if ($budgetTransfer === null || $currencyIsoCode === null) {
            return null;
        }

        return $this->getFactory()->createBudgetSummaryFormatter()->formatBudgetSummary($budgetTransfer, $currencyIsoCode);
    }

    public static function getName(): string
    {
        return 'CostCenterDetailWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/cost-center-detail/cost-center-detail.twig';
    }
}

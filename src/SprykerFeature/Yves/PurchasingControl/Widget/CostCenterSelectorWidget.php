<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use ArrayObject;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterSelectorWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTERS = 'costCenters';

    protected const string PARAMETER_SELECTED_COST_CENTER = 'selectedCostCenter';

    protected const string PARAMETER_BUDGETS = 'budgets';

    protected const string PARAMETER_SELECTED_BUDGET = 'selectedBudget';

    protected const string PARAMETER_QUOTE = 'quote';

    protected const string PARAMETER_IS_LOCKED = 'isLocked';

    public function __construct(QuoteTransfer $quoteTransfer)
    {
        $this->addParameter(static::PARAMETER_QUOTE, $quoteTransfer);
        $this->addParameter(static::PARAMETER_IS_LOCKED, $this->isQuoteInApprovalProcess($quoteTransfer));

        $customerTransfer = $this->getFactory()->getCustomerClient()->getCustomer();

        if (!$customerTransfer || !$customerTransfer->getCompanyUserTransfer()) {
            $this->addEmptyParameters();

            return;
        }

        $idCompanyBusinessUnit = $customerTransfer->getCompanyUserTransfer()->getFkCompanyBusinessUnitOrFail();
        $currencyIsoCode = $quoteTransfer->getCurrency() ? $quoteTransfer->getCurrencyOrFail()->getCode() ?? '' : '';

        $costCenters = $this->getFactory()
            ->getPurchasingControlClient()
            ->getActiveCostCentersForCompanyBusinessUnit($idCompanyBusinessUnit, $currencyIsoCode)
            ->getCostCenters();

        $selectedCostCenter = $this->resolveSelectedCostCenter($costCenters, $quoteTransfer->getIdCostCenter());
        $budgets = new ArrayObject();
        $selectedBudget = null;

        if ($selectedCostCenter) {
            $budgets = $this->getFactory()
                ->getPurchasingControlClient()
                ->getActiveBudgetsForCostCenter($selectedCostCenter->getIdCostCenterOrFail(), $currencyIsoCode)
                ->getBudgets();

            $selectedBudget = $this->resolveSelectedBudget($budgets, $quoteTransfer->getIdBudget());
        }

        $this->addParameter(static::PARAMETER_COST_CENTERS, $costCenters);
        $this->addParameter(static::PARAMETER_SELECTED_COST_CENTER, $selectedCostCenter);
        $this->addParameter(static::PARAMETER_BUDGETS, $budgets);
        $this->addParameter(static::PARAMETER_SELECTED_BUDGET, $selectedBudget);
    }

    public static function getName(): string
    {
        return 'CostCenterSelectorWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/components/molecules/cost-center-selector/cost-center-selector.twig';
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenters
     */
    protected function resolveSelectedCostCenter(ArrayObject $costCenters, ?int $idCostCenter): ?CostCenterTransfer
    {
        if ($costCenters->count() === 1) {
            return $costCenters[0];
        }

        if (!$idCostCenter) {
            return null;
        }

        foreach ($costCenters as $costCenter) {
            if ($costCenter->getIdCostCenter() === $idCostCenter) {
                return $costCenter;
            }
        }

        return null;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\BudgetTransfer> $budgets
     */
    protected function resolveSelectedBudget(ArrayObject $budgets, ?int $idBudget): ?BudgetTransfer
    {
        if ($budgets->count() === 1) {
            return $budgets[0];
        }

        if (!$idBudget) {
            return null;
        }

        foreach ($budgets as $budget) {
            if ($budget->getIdBudget() === $idBudget) {
                return $budget;
            }
        }

        return null;
    }

    protected function addEmptyParameters(): void
    {
        $this->addParameter(static::PARAMETER_COST_CENTERS, new ArrayObject());
        $this->addParameter(static::PARAMETER_SELECTED_COST_CENTER, null);
        $this->addParameter(static::PARAMETER_BUDGETS, new ArrayObject());
        $this->addParameter(static::PARAMETER_SELECTED_BUDGET, null);
    }

    protected function isQuoteInApprovalProcess(QuoteTransfer $quoteTransfer): bool
    {
        if ($quoteTransfer->getIsLocked()) {
            return true;
        }

        return false;
    }
}

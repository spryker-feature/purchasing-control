<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterSummaryWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTER = 'costCenter';

    protected const string PARAMETER_BUDGET = 'budget';

    public function __construct(QuoteTransfer $quoteTransfer)
    {
        if ($quoteTransfer->getIdCostCenter() === null) {
            $this->addParameter(static::PARAMETER_COST_CENTER, null);
            $this->addParameter(static::PARAMETER_BUDGET, null);

            return;
        }

        $customerTransfer = $this->getFactory()->getCustomerClient()->getCustomer();

        if (!$customerTransfer || !$customerTransfer->getCompanyUserTransfer()) {
            $this->addParameter(static::PARAMETER_COST_CENTER, null);
            $this->addParameter(static::PARAMETER_BUDGET, null);

            return;
        }

        $idCompanyBusinessUnit = $customerTransfer->getCompanyUserTransfer()->getFkCompanyBusinessUnitOrFail();
        $currencyIsoCode = $quoteTransfer->getCurrency() ? $quoteTransfer->getCurrencyOrFail()->getCode() ?? '' : '';
        $idCostCenter = $quoteTransfer->getIdCostCenterOrFail();

        $costCenter = $this->resolveCostCenter($idCompanyBusinessUnit, $currencyIsoCode, $idCostCenter);
        $budget = null;

        if ($costCenter && $quoteTransfer->getIdBudget() !== null) {
            $budget = $this->resolveBudget($idCostCenter, $currencyIsoCode, $quoteTransfer->getIdBudgetOrFail());
        }

        $this->addParameter(static::PARAMETER_COST_CENTER, $costCenter);
        $this->addParameter(static::PARAMETER_BUDGET, $budget);
    }

    public static function getName(): string
    {
        return 'CostCenterSummaryWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/components/molecules/cost-center-summary/cost-center-summary.twig';
    }

    protected function resolveCostCenter(int $idCompanyBusinessUnit, string $currencyIsoCode, int $idCostCenter): ?CostCenterTransfer
    {
        $costCenters = $this->getFactory()
            ->getPurchasingControlClient()
            ->getActiveCostCentersForCompanyBusinessUnit($idCompanyBusinessUnit, $currencyIsoCode)
            ->getCostCenters();

        foreach ($costCenters as $costCenter) {
            if ($costCenter->getIdCostCenter() === $idCostCenter) {
                return $costCenter;
            }
        }

        return null;
    }

    protected function resolveBudget(int $idCostCenter, string $currencyIsoCode, int $idBudget): ?BudgetTransfer
    {
        $budgets = $this->getFactory()
            ->getPurchasingControlClient()
            ->getActiveBudgetsForCostCenter($idCostCenter, $currencyIsoCode)
            ->getBudgets();

        foreach ($budgets as $budget) {
            if ($budget->getIdBudget() === $idBudget) {
                return $budget;
            }
        }

        return null;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class PurchasingControlSummaryWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTER_DETAILS = 'costCenterDetails';

    protected const string PARAMETER_BUDGET_SUMMARIES = 'budgetSummaries';

    public function __construct()
    {
        $summaryReader = $this->getFactory()->createCostCenterSummaryReader();

        $this->addParameter(static::PARAMETER_COST_CENTER_DETAILS, $summaryReader->getCostCenterBudgetDetails());
        $this->addParameter(static::PARAMETER_BUDGET_SUMMARIES, $summaryReader->getBudgetSummaries());
    }

    public static function getName(): string
    {
        return 'PurchasingControlSummaryWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/purchasing-control-summary/purchasing-control-summary.twig';
    }
}

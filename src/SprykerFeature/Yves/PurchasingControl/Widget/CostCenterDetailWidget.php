<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterDetailWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTER_NAME = 'costCenterName';

    protected const string PARAMETER_BUDGET_NAME = 'budgetName';

    public function __construct(QuoteTransfer $quoteTransfer)
    {
        $costCenterReader = $this->getFactory()->createCostCenterReader();
        $costCenterTransfer = $costCenterReader->findCostCenterWithBudgets($quoteTransfer->getIdCostCenter());

        $this->addParameter(static::PARAMETER_COST_CENTER_NAME, $costCenterTransfer?->getName());
        $this->addParameter(
            static::PARAMETER_BUDGET_NAME,
            $this->getFactory()->createBudgetReader()->resolveBudgetName($costCenterTransfer, $quoteTransfer->getIdBudget()),
        );
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

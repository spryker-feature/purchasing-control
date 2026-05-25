<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Spryker\Yves\Kernel\Widget\AbstractWidget;

class CostCenterBudgetFilterWidget extends AbstractWidget
{
    public static function getName(): string
    {
        return 'CostCenterBudgetFilterWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/cost-center-budget-filter-widget/cost-center-budget-filter-widget.twig';
    }
}

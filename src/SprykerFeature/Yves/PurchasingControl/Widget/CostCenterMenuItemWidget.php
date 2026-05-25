<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Spryker\Yves\Kernel\Widget\AbstractWidget;

class CostCenterMenuItemWidget extends AbstractWidget
{
    protected const string PARAMETER_IS_ACTIVE_PAGE = 'isActivePage';

    protected const string PAGE_KEY = 'cost-center';

    public function __construct(string $activePage)
    {
        $this->addParameter(
            static::PARAMETER_IS_ACTIVE_PAGE,
            $activePage === static::PAGE_KEY,
        );
    }

    public static function getName(): string
    {
        return 'CostCenterMenuItemWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/cost-center-menu-item/cost-center-menu-item.twig';
    }
}

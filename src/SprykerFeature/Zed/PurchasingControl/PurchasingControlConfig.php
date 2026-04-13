<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class PurchasingControlConfig extends AbstractBundleConfig
{
    protected const DEFAULT_BUSINESS_UNIT_SELECT_LIMIT = 100;

    /**
     * Specification:
     * - Returns the maximum number of business units loaded into the cost center form dropdown.
     * - Projects with large BU datasets should override this method or switch to async autocomplete.
     *
     * @api
     */
    public function getBusinessUnitSelectLimit(): int
    {
        return static::DEFAULT_BUSINESS_UNIT_SELECT_LIMIT;
    }
}

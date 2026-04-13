<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Shared\PurchasingControl;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class PurchasingControlConfig extends AbstractSharedConfig
{
    /**
     * Specification:
     * - Enforcement rule that blocks order placement when budget is exceeded.
     *
     * @api
     *
     * @var string
     */
    public const string ENFORCEMENT_RULE_BLOCK = 'block';

    /**
     * Specification:
     * - Enforcement rule that warns the user when budget is exceeded but allows order placement.
     *
     * @api
     *
     * @var string
     */
    public const string ENFORCEMENT_RULE_WARN = 'warn';

    /**
     * Specification:
     * - Enforcement rule that requires approval when budget is exceeded.
     *
     * @api
     *
     * @var string
     */
    public const string ENFORCEMENT_RULE_REQUIRE_APPROVAL = 'require_approval';
}

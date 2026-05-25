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

    /**
     * Specification:
     * - Maximum number of characters allowed for cost center and budget names.
     *
     * @api
     *
     * @var int
     */
    public const int NAME_MAX_LENGTH = 255;

    /**
     * Specification:
     * - Maximum budget amount in the smallest currency unit (e.g. cents).
     * - Matches the INTEGER column capacity in the database.
     *
     * @api
     *
     * @var int
     */
    public const int BUDGET_AMOUNT_MAX = PHP_INT_MAX;
}

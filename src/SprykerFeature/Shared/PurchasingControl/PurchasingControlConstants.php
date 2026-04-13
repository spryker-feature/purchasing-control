<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Shared\PurchasingControl;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface PurchasingControlConstants
{
    /**
     * @api
     *
     * @var string
     */
    public const ENFORCEMENT_RULE_BLOCK = 'block';

    /**
     * @api
     *
     * @var string
     */
    public const ENFORCEMENT_RULE_WARN = 'warn';

    /**
     * @api
     *
     * @var string
     */
    public const ENFORCEMENT_RULE_REQUIRE_APPROVAL = 'require_approval';
}

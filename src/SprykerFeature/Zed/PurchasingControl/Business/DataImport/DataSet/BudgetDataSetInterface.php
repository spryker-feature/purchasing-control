<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet;

interface BudgetDataSetInterface
{
    public const string COLUMN_COST_CENTER_KEY = 'cost_center_key';

    public const string COLUMN_NAME = 'name';

    public const string COLUMN_AMOUNT = 'amount';

    public const string COLUMN_CURRENCY_ISO_CODE = 'currency_iso_code';

    public const string COLUMN_STARTS_AT = 'starts_at';

    public const string COLUMN_ENDS_AT = 'ends_at';

    public const string COLUMN_ENFORCEMENT_RULE = 'enforcement_rule';

    public const string COLUMN_IS_ACTIVE = 'is_active';

    public const string KEY_ID_COST_CENTER = 'id_cost_center';
}

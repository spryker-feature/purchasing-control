<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Shared\PurchasingControl\Plugin\Permission;

use Spryker\Shared\PermissionExtension\Dependency\Plugin\PermissionPluginInterface;
use Spryker\Yves\Kernel\AbstractPlugin;

class ManageCostCentersPermissionPlugin extends AbstractPlugin implements PermissionPluginInterface
{
    public const string KEY = 'ManageCostCentersPermissionPlugin';

    public function getKey(): string
    {
        return static::KEY;
    }
}

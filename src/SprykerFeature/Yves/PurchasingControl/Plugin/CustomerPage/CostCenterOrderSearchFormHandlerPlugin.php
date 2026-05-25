<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Plugin\CustomerPage;

use Generated\Shared\Transfer\OrderListTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\OrderSearchFormHandlerPluginInterface;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterOrderSearchFormHandlerPlugin extends AbstractPlugin implements OrderSearchFormHandlerPluginInterface
{
    /**
     * {@inheritDoc}
     * - Maps selected cost center and budget IDs from the filter form to FilterFieldTransfer entries on the OrderListTransfer.
     *
     * @api
     *
     * @param array<string, mixed> $orderSearchFormData
     */
    public function handle(array $orderSearchFormData, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        return $this->getFactory()->createCostCenterOrderSearchFormHandler()->handle($orderSearchFormData, $orderListTransfer);
    }
}

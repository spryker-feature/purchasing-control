<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl;

use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Client\Quote\QuoteClientInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface getClient()
 */
class PurchasingControlFactory extends AbstractFactory
{
    public function getPurchasingControlClient(): PurchasingControlClientInterface
    {
        return $this->getClient();
    }

    public function getCustomerClient(): CustomerClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_CUSTOMER);
    }

    public function getQuoteClient(): QuoteClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_QUOTE);
    }
}

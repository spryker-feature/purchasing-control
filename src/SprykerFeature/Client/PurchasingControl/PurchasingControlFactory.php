<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use SprykerFeature\Client\PurchasingControl\Zed\PurchasingControlStub;
use SprykerFeature\Client\PurchasingControl\Zed\PurchasingControlStubInterface;

class PurchasingControlFactory extends AbstractFactory
{
    public function createPurchasingControlStub(): PurchasingControlStubInterface
    {
        return new PurchasingControlStub($this->getZedRequestClient());
    }

    public function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_ZED_REQUEST);
    }
}

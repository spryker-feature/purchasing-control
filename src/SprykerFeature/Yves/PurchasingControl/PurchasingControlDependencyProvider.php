<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl;

use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;

class PurchasingControlDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string CLIENT_CUSTOMER = 'CLIENT_CUSTOMER';

    public const string CLIENT_QUOTE = 'CLIENT_QUOTE';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addCustomerClient($container);
        $container = $this->addQuoteClient($container);

        return $container;
    }

    protected function addCustomerClient(Container $container): Container
    {
        $container->set(static::CLIENT_CUSTOMER, static function (Container $container) {
            return $container->getLocator()->customer()->client();
        });

        return $container;
    }

    protected function addQuoteClient(Container $container): Container
    {
        $container->set(static::CLIENT_QUOTE, static function (Container $container) {
            return $container->getLocator()->quote()->client();
        });

        return $container;
    }
}

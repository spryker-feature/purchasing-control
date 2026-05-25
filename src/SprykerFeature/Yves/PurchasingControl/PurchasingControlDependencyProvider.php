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

    public const string CLIENT_MONEY = 'CLIENT_MONEY';

    public const string CLIENT_QUOTE = 'CLIENT_QUOTE';

    public const string CLIENT_COMPANY_BUSINESS_UNIT = 'CLIENT_COMPANY_BUSINESS_UNIT';

    public const string CLIENT_STORE = 'CLIENT_STORE';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addCustomerClient($container);
        $container = $this->addMoneyClient($container);
        $container = $this->addQuoteClient($container);
        $container = $this->addCompanyBusinessUnitClient($container);
        $container = $this->addStoreClient($container);

        return $container;
    }

    protected function addCustomerClient(Container $container): Container
    {
        $container->set(static::CLIENT_CUSTOMER, static function (Container $container) {
            return $container->getLocator()->customer()->client();
        });

        return $container;
    }

    protected function addMoneyClient(Container $container): Container
    {
        $container->set(static::CLIENT_MONEY, static function (Container $container) {
            return $container->getLocator()->money()->client();
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

    protected function addCompanyBusinessUnitClient(Container $container): Container
    {
        $container->set(static::CLIENT_COMPANY_BUSINESS_UNIT, static function (Container $container) {
            return $container->getLocator()->companyBusinessUnit()->client();
        });

        return $container;
    }

    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, static function (Container $container) {
            return $container->getLocator()->store()->client();
        });

        return $container;
    }
}

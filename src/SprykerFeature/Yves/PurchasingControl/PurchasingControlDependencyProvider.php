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

    public const string CLIENT_CURRENCY = 'CLIENT_CURRENCY';

    public const string CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY_STORAGE';

    public const string CLIENT_LOCALE = 'CLIENT_LOCALE';

    public const string SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    public const string CLIENT_COMPANY_USER = 'CLIENT_COMPANY_USER';

    public const string CLIENT_QUOTE_REQUEST = 'CLIENT_QUOTE_REQUEST';

    public const string CLIENT_QUOTE_REQUEST_AGENT = 'CLIENT_QUOTE_REQUEST_AGENT';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addCustomerClient($container);
        $container = $this->addMoneyClient($container);
        $container = $this->addQuoteClient($container);
        $container = $this->addCompanyBusinessUnitClient($container);
        $container = $this->addStoreClient($container);
        $container = $this->addCurrencyClient($container);
        $container = $this->addGlossaryStorageClient($container);
        $container = $this->addLocaleClient($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addCompanyUserClient($container);
        $container = $this->addQuoteRequestClient($container);
        $container = $this->addQuoteRequestAgentClient($container);

        return $container;
    }

    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, static function (Container $container) {
            return $container->getLocator()->glossaryStorage()->client();
        });

        return $container;
    }

    protected function addLocaleClient(Container $container): Container
    {
        $container->set(static::CLIENT_LOCALE, static function (Container $container) {
            return $container->getLocator()->locale()->client();
        });

        return $container;
    }

    protected function addCurrencyClient(Container $container): Container
    {
        $container->set(static::CLIENT_CURRENCY, static function (Container $container) {
            return $container->getLocator()->currency()->client();
        });

        return $container;
    }

    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, static function (Container $container) {
            return $container->getLocator()->utilEncoding()->service();
        });

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

    protected function addCompanyUserClient(Container $container): Container
    {
        $container->set(static::CLIENT_COMPANY_USER, static function (Container $container) {
            return $container->getLocator()->companyUser()->client();
        });

        return $container;
    }

    protected function addQuoteRequestClient(Container $container): Container
    {
        $container->set(static::CLIENT_QUOTE_REQUEST, static function (Container $container) {
            return $container->getLocator()->quoteRequest()->client();
        });

        return $container;
    }

    protected function addQuoteRequestAgentClient(Container $container): Container
    {
        $container->set(static::CLIENT_QUOTE_REQUEST_AGENT, static function (Container $container) {
            return $container->getLocator()->quoteRequestAgent()->client();
        });

        return $container;
    }
}

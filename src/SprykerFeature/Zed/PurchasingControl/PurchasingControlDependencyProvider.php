<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl;

use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class PurchasingControlDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_COMPANY = 'FACADE_COMPANY';

    public const FACADE_COMPANY_BUSINESS_UNIT = 'FACADE_COMPANY_BUSINESS_UNIT';

    public const FACADE_COMPANY_USER = 'FACADE_COMPANY_USER';

    public const FACADE_CURRENCY = 'FACADE_CURRENCY';

    public const FACADE_GLOSSARY = 'FACADE_GLOSSARY';

    public const FACADE_LOCALE = 'FACADE_LOCALE';

    public const FACADE_QUOTE = 'FACADE_QUOTE';

    public const FACADE_QUOTE_APPROVAL = 'FACADE_QUOTE_APPROVAL';

    public const FACADE_MONEY = 'FACADE_MONEY';

    public const FACADE_SALES = 'FACADE_SALES';

    public const SERVICE_UTIL_DATE_TIME = 'SERVICE_UTIL_DATE_TIME';

    public const PROPEL_QUERY_COMPANY_BUSINESS_UNIT = 'PROPEL_QUERY_COMPANY_BUSINESS_UNIT';

    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);
        $container = $this->addCompanyBusinessUnitQuery($container);

        return $container;
    }

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addQuoteFacade($container);
        $container = $this->addQuoteApprovalFacade($container);
        $container = $this->addCompanyFacade($container);
        $container = $this->addCompanyUserFacade($container);
        $container = $this->addSalesFacade($container);
        $container = $this->addCurrencyFacade($container);

        return $container;
    }

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addCompanyBusinessUnitFacade($container);
        $container = $this->addCurrencyFacade($container);
        $container = $this->addGlossaryFacade($container);
        $container = $this->addLocaleFacade($container);
        $container = $this->addMoneyFacade($container);
        $container = $this->addUtilDateTimeService($container);

        return $container;
    }

    /**
     * @module CompanyBusinessUnit
     */
    protected function addCompanyBusinessUnitQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_COMPANY_BUSINESS_UNIT, $container->factory(static function (): SpyCompanyBusinessUnitQuery {
            return SpyCompanyBusinessUnitQuery::create();
        }));

        return $container;
    }

    protected function addQuoteFacade(Container $container): Container
    {
        $container->set(static::FACADE_QUOTE, static function (Container $container) {
            return $container->getLocator()->quote()->facade();
        });

        return $container;
    }

    protected function addQuoteApprovalFacade(Container $container): Container
    {
        $container->set(static::FACADE_QUOTE_APPROVAL, static function (Container $container) {
            return $container->getLocator()->quoteApproval()->facade();
        });

        return $container;
    }

    protected function addCompanyFacade(Container $container): Container
    {
        $container->set(static::FACADE_COMPANY, static function (Container $container) {
            return $container->getLocator()->company()->facade();
        });

        return $container;
    }

    protected function addCompanyUserFacade(Container $container): Container
    {
        $container->set(static::FACADE_COMPANY_USER, static function (Container $container) {
            return $container->getLocator()->companyUser()->facade();
        });

        return $container;
    }

    protected function addCurrencyFacade(Container $container): Container
    {
        $container->set(static::FACADE_CURRENCY, static function (Container $container) {
            return $container->getLocator()->currency()->facade();
        });

        return $container;
    }

    protected function addCompanyBusinessUnitFacade(Container $container): Container
    {
        $container->set(static::FACADE_COMPANY_BUSINESS_UNIT, static function (Container $container) {
            return $container->getLocator()->companyBusinessUnit()->facade();
        });

        return $container;
    }

    protected function addSalesFacade(Container $container): Container
    {
        $container->set(static::FACADE_SALES, static function (Container $container) {
            return $container->getLocator()->sales()->facade();
        });

        return $container;
    }

    protected function addGlossaryFacade(Container $container): Container
    {
        $container->set(static::FACADE_GLOSSARY, static function (Container $container) {
            return $container->getLocator()->glossary()->facade();
        });

        return $container;
    }

    protected function addLocaleFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCALE, static function (Container $container) {
            return $container->getLocator()->locale()->facade();
        });

        return $container;
    }

    protected function addMoneyFacade(Container $container): Container
    {
        $container->set(static::FACADE_MONEY, static function (Container $container) {
            return $container->getLocator()->money()->facade();
        });

        return $container;
    }

    protected function addUtilDateTimeService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_DATE_TIME, static function (Container $container) {
            return $container->getLocator()->utilDateTime()->service();
        });

        return $container;
    }
}

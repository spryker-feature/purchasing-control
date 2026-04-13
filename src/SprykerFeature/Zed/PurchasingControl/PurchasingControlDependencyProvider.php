<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class PurchasingControlDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_COMPANY_BUSINESS_UNIT = 'FACADE_COMPANY_BUSINESS_UNIT';

    public const FACADE_CURRENCY = 'FACADE_CURRENCY';

    public const FACADE_QUOTE_APPROVAL = 'FACADE_QUOTE_APPROVAL';

    public const SERVICE_UTIL_DATE_TIME = 'SERVICE_UTIL_DATE_TIME';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addQuoteApprovalFacade($container);

        return $container;
    }

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addCompanyBusinessUnitFacade($container);
        $container = $this->addCurrencyFacade($container);
        $container = $this->addUtilDateTimeService($container);

        return $container;
    }

    protected function addQuoteApprovalFacade(Container $container): Container
    {
        $container->set(static::FACADE_QUOTE_APPROVAL, static function (Container $container) {
            return $container->getLocator()->quoteApproval()->facade();
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

    protected function addUtilDateTimeService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_DATE_TIME, static function (Container $container) {
            return $container->getLocator()->utilDateTime()->service();
        });

        return $container;
    }
}

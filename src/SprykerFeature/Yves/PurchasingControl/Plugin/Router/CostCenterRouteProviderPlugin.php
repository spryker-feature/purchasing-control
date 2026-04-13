<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Plugin\Router;

use Spryker\Yves\Router\Plugin\RouteProvider\AbstractRouteProviderPlugin;
use Spryker\Yves\Router\Route\RouteCollection;

class CostCenterRouteProviderPlugin extends AbstractRouteProviderPlugin
{
    public const string ROUTE_NAME_COST_CENTER_UPDATE_QUOTE = 'cost-center-update-quote';

    protected const string PATTERN_COST_CENTER_UPDATE_QUOTE = '/cost-center/update-quote';

    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = $this->addUpdateQuoteRoute($routeCollection);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\CostCenterController::updateQuoteAction()
     */
    protected function addUpdateQuoteRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildPostRoute(static::PATTERN_COST_CENTER_UPDATE_QUOTE, 'PurchasingControl', 'CostCenter', 'updateQuote');

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE, $route);

        return $routeCollection;
    }
}

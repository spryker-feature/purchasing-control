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
    public const string ROUTE_NAME_COST_CENTER_UPDATE_QUOTE = 'company/cost-center/update-quote';

    public const string ROUTE_NAME_COST_CENTER_LIST = 'company/cost-center';

    public const string ROUTE_NAME_COST_CENTER_CREATE = 'company/cost-center/create';

    public const string ROUTE_NAME_COST_CENTER_UPDATE = 'company/cost-center/update';

    protected const string PATTERN_COST_CENTER_UPDATE_QUOTE = '/company/cost-center/update-quote';

    protected const string PATTERN_COST_CENTER_LIST = '/company/cost-center';

    protected const string PATTERN_COST_CENTER_CREATE = '/company/cost-center/create';

    protected const string PATTERN_COST_CENTER_UPDATE = '/company/cost-center/update';

    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = $this->addUpdateQuoteRoute($routeCollection);
        $routeCollection = $this->addListRoute($routeCollection);
        $routeCollection = $this->addCreateRoute($routeCollection);
        $routeCollection = $this->addUpdateRoute($routeCollection);

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

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\CostCenterListController::indexAction()
     */
    protected function addListRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_COST_CENTER_LIST, 'PurchasingControl', 'CostCenterList', 'index');

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_LIST, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\CostCenterCreateController::indexAction()
     */
    protected function addCreateRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_COST_CENTER_CREATE, 'PurchasingControl', 'CostCenterCreate', 'index');

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_CREATE, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\CostCenterUpdateController::indexAction()
     */
    protected function addUpdateRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_COST_CENTER_UPDATE, 'PurchasingControl', 'CostCenterUpdate', 'index');

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE, $route);

        return $routeCollection;
    }
}

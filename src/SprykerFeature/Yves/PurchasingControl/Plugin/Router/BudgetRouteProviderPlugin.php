<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Plugin\Router;

use Spryker\Yves\Router\Plugin\RouteProvider\AbstractRouteProviderPlugin;
use Spryker\Yves\Router\Route\RouteCollection;

class BudgetRouteProviderPlugin extends AbstractRouteProviderPlugin
{
    public const string ROUTE_NAME_BUDGET_LIST = 'company/cost-center/budget';

    public const string ROUTE_NAME_BUDGET_CREATE = 'company/cost-center/budget/create';

    public const string ROUTE_NAME_BUDGET_UPDATE = 'company/cost-center/budget/update';

    protected const string PATTERN_BUDGET_LIST = '/company/cost-center/budget';

    protected const string PATTERN_BUDGET_CREATE = '/company/cost-center/budget/create';

    protected const string PATTERN_BUDGET_UPDATE = '/company/cost-center/budget/update';

    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = $this->addListRoute($routeCollection);
        $routeCollection = $this->addCreateRoute($routeCollection);
        $routeCollection = $this->addUpdateRoute($routeCollection);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\BudgetListController::indexAction()
     */
    protected function addListRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_BUDGET_LIST, 'PurchasingControl', 'BudgetList', 'index');

        $routeCollection->add(static::ROUTE_NAME_BUDGET_LIST, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\BudgetCreateController::indexAction()
     */
    protected function addCreateRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_BUDGET_CREATE, 'PurchasingControl', 'BudgetCreate', 'index');

        $routeCollection->add(static::ROUTE_NAME_BUDGET_CREATE, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\BudgetUpdateController::indexAction()
     */
    protected function addUpdateRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_BUDGET_UPDATE, 'PurchasingControl', 'BudgetUpdate', 'index');

        $routeCollection->add(static::ROUTE_NAME_BUDGET_UPDATE, $route);

        return $routeCollection;
    }
}

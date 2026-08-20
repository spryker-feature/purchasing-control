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

    public const string ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST = 'company/cost-center/update-quote-request';

    public const string ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT = 'company/cost-center/update-quote-request-from-edit';

    public const string ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST = 'agent/quote-request/cost-center/update';

    public const string ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT = 'agent/quote-request/cost-center/update-from-edit';

    public const string ROUTE_NAME_COST_CENTER_LIST = 'company/cost-center';

    public const string ROUTE_NAME_COST_CENTER_CREATE = 'company/cost-center/create';

    public const string ROUTE_NAME_COST_CENTER_UPDATE = 'company/cost-center/update';

    protected const string PATTERN_COST_CENTER_UPDATE_QUOTE = '/company/cost-center/update-quote';

    protected const string PATTERN_COST_CENTER_UPDATE_QUOTE_REQUEST = '/company/cost-center/update-quote-request/{quoteRequestReference}';

    protected const string PATTERN_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT = '/company/cost-center/update-quote-request-from-edit/{quoteRequestReference}';

    /**
     * @uses \SprykerShop\Yves\AgentPage\AgentPageConfig::getAgentFirewallRegex()
     */
    protected const string PATTERN_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST = '/agent/quote-request/cost-center/update/{quoteRequestReference}';

    /**
     * @uses \SprykerShop\Yves\AgentPage\AgentPageConfig::getAgentFirewallRegex()
     */
    protected const string PATTERN_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT = '/agent/quote-request/cost-center/update-from-edit/{quoteRequestReference}';

    // Route default naming the page the update returns to. Set from the route definition, never from the request.
    protected const string PARAM_REDIRECT_ROUTE = 'redirectRoute';

    /**
     * @uses \SprykerShop\Yves\QuoteRequestPage\Plugin\Router\QuoteRequestPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_DETAILS
     */
    protected const string ROUTE_QUOTE_REQUEST_DETAILS = 'quote-request/details';

    /**
     * @uses \SprykerShop\Yves\QuoteRequestPage\Plugin\Router\QuoteRequestPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_EDIT
     */
    protected const string ROUTE_QUOTE_REQUEST_EDIT = 'quote-request/edit';

    /**
     * @uses \SprykerShop\Yves\QuoteRequestAgentPage\Plugin\Router\QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_DETAILS
     */
    protected const string ROUTE_QUOTE_REQUEST_AGENT_DETAILS = 'agent/quote-request/details';

    /**
     * @uses \SprykerShop\Yves\QuoteRequestAgentPage\Plugin\Router\QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_EDIT
     */
    protected const string ROUTE_QUOTE_REQUEST_AGENT_EDIT = 'agent/quote-request/edit';

    protected const string PATTERN_COST_CENTER_LIST = '/company/cost-center';

    protected const string PATTERN_COST_CENTER_CREATE = '/company/cost-center/create';

    protected const string PATTERN_COST_CENTER_UPDATE = '/company/cost-center/update';

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = $this->addUpdateQuoteRoute($routeCollection);
        $routeCollection = $this->addUpdateQuoteRequestRoute($routeCollection);
        $routeCollection = $this->addUpdateQuoteRequestFromEditRoute($routeCollection);
        $routeCollection = $this->addUpdateAgentQuoteRequestRoute($routeCollection);
        $routeCollection = $this->addUpdateAgentQuoteRequestFromEditRoute($routeCollection);
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
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestCostCenterController::updateQuoteRequestAction()
     */
    protected function addUpdateQuoteRequestRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildPostRoute(static::PATTERN_COST_CENTER_UPDATE_QUOTE_REQUEST, 'PurchasingControl', 'QuoteRequestCostCenter', 'updateQuoteRequest');
        $route->setDefault(static::PARAM_REDIRECT_ROUTE, static::ROUTE_QUOTE_REQUEST_DETAILS);

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestCostCenterController::updateQuoteRequestAction()
     */
    protected function addUpdateQuoteRequestFromEditRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildPostRoute(static::PATTERN_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT, 'PurchasingControl', 'QuoteRequestCostCenter', 'updateQuoteRequest');
        $route->setDefault(static::PARAM_REDIRECT_ROUTE, static::ROUTE_QUOTE_REQUEST_EDIT);

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestAgentCostCenterController::updateQuoteRequestAction()
     */
    protected function addUpdateAgentQuoteRequestRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildPostRoute(static::PATTERN_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST, 'PurchasingControl', 'QuoteRequestAgentCostCenter', 'updateQuoteRequest');
        $route->setDefault(static::PARAM_REDIRECT_ROUTE, static::ROUTE_QUOTE_REQUEST_AGENT_DETAILS);

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST, $route);

        return $routeCollection;
    }

    /**
     * @uses \SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestAgentCostCenterController::updateQuoteRequestAction()
     */
    protected function addUpdateAgentQuoteRequestFromEditRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildPostRoute(static::PATTERN_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT, 'PurchasingControl', 'QuoteRequestAgentCostCenter', 'updateQuoteRequest');
        $route->setDefault(static::PARAM_REDIRECT_ROUTE, static::ROUTE_QUOTE_REQUEST_AGENT_EDIT);

        $routeCollection->add(static::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT, $route);

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

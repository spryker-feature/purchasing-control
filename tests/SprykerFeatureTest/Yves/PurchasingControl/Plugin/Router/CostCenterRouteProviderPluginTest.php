<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Plugin\Router;

use Codeception\Test\Unit;
use Spryker\Yves\Router\Route\Route;
use Spryker\Yves\Router\Route\RouteCollection;
use SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestAgentCostCenterController;
use SprykerFeature\Yves\PurchasingControl\Controller\QuoteRequestCostCenterController;
use SprykerFeature\Yves\PurchasingControl\Plugin\Router\CostCenterRouteProviderPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Plugin
 * @group Router
 * @group CostCenterRouteProviderPluginTest
 */
class CostCenterRouteProviderPluginTest extends Unit
{
    protected const string PARAM_REDIRECT_ROUTE = 'redirectRoute';

    public function testAddRoutesRegistersQuoteRequestCostCenterUpdateAsPostRoute(): void
    {
        // Act
        $route = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST);

        // Assert
        $this->assertNotNull($route);
        $this->assertSame('/company/cost-center/update-quote-request/{quoteRequestReference}', $route->getPath());
        $this->assertSame([Request::METHOD_POST], $route->getMethods());
    }

    public function testAddRoutesResolvesQuoteRequestCostCenterController(): void
    {
        // Act
        $route = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST);

        // Assert
        $this->assertSame(
            [QuoteRequestCostCenterController::class, 'updateQuoteRequestAction'],
            $route->getDefaults()['_controller'],
        );
    }

    public function testAddRoutesKeepsExistingQuoteUpdateRoute(): void
    {
        // Act
        $route = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE);

        // Assert
        $this->assertNotNull($route);
        $this->assertSame('/company/cost-center/update-quote', $route->getPath());
    }

    public function testAddRoutesRegistersAgentRoutesBehindAgentPathPrefix(): void
    {
        // Act
        $route = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST);
        $fromEditRoute = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT);

        // Assert
        $this->assertNotNull($route);
        $this->assertNotNull($fromEditRoute);
        $this->assertStringStartsWith('/agent/', $route->getPath());
        $this->assertStringStartsWith('/agent/', $fromEditRoute->getPath());
        $this->assertSame([Request::METHOD_POST], $route->getMethods());
        $this->assertSame([Request::METHOD_POST], $fromEditRoute->getMethods());
    }

    public function testAddRoutesResolvesAgentQuoteRequestCostCenterController(): void
    {
        // Act
        $route = $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST);

        // Assert
        $this->assertSame(
            [QuoteRequestAgentCostCenterController::class, 'updateQuoteRequestAction'],
            $route->getDefaults()['_controller'],
        );
    }

    /**
     * @dataProvider redirectRouteDefaultDataProvider
     */
    public function testAddRoutesSetsRedirectRouteDefaultForEachContext(string $routeName, string $expectedRedirectRoute): void
    {
        // Act
        $route = $this->findRoute($routeName);

        // Assert
        $this->assertNotNull($route);
        $this->assertSame($expectedRedirectRoute, $route->getDefaults()[static::PARAM_REDIRECT_ROUTE] ?? null);
    }

    /**
     * @return array<string, array<string>>
     */
    public function redirectRouteDefaultDataProvider(): array
    {
        return [
            'buyer details' => [
                CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST,
                'quote-request/details',
            ],
            'buyer edit' => [
                CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT,
                'quote-request/edit',
            ],
            'agent details' => [
                CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST,
                'agent/quote-request/details',
            ],
            'agent edit' => [
                CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT,
                'agent/quote-request/edit',
            ],
        ];
    }

    public function testAddRoutesGivesEachContextItsOwnPath(): void
    {
        // Act
        $paths = [
            $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST)?->getPath(),
            $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_QUOTE_REQUEST_FROM_EDIT)?->getPath(),
            $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST)?->getPath(),
            $this->findRoute(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST_FROM_EDIT)?->getPath(),
        ];

        // Assert
        $this->assertSame($paths, array_unique($paths));
    }

    protected function findRoute(string $routeName): ?Route
    {
        return (new CostCenterRouteProviderPlugin())->addRoutes(new RouteCollection())->get($routeName);
    }
}

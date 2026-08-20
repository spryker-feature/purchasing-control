<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\QuoteRequestTransfer;
use SprykerFeature\Yves\PurchasingControl\Plugin\Router\CostCenterRouteProviderPlugin;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class QuoteRequestAgentCostCenterSelectorWidget extends AbstractQuoteRequestCostCenterSelectorWidget
{
    public static function getName(): string
    {
        return 'QuoteRequestAgentCostCenterSelectorWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/quote-request-cost-center-selector/quote-request-cost-center-selector.twig';
    }

    protected function isQuoteRequestEditable(QuoteRequestTransfer $quoteRequestTransfer): bool
    {
        return $this->getFactory()->getQuoteRequestAgentClient()->isQuoteRequestEditable($quoteRequestTransfer);
    }

    protected function getDefaultFormActionRoute(): string
    {
        return CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_UPDATE_AGENT_QUOTE_REQUEST;
    }
}

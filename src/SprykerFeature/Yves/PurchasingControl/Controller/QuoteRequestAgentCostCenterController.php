<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use SprykerFeature\Yves\PurchasingControl\Reader\QuoteRequestReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Writer\QuoteRequestCostCenterWriterInterface;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class QuoteRequestAgentCostCenterController extends AbstractQuoteRequestCostCenterController
{
    protected function createQuoteRequestReader(): QuoteRequestReaderInterface
    {
        return $this->getFactory()->createAgentQuoteRequestReader();
    }

    protected function createQuoteRequestCostCenterWriter(): QuoteRequestCostCenterWriterInterface
    {
        return $this->getFactory()->createAgentQuoteRequestCostCenterWriter();
    }
}

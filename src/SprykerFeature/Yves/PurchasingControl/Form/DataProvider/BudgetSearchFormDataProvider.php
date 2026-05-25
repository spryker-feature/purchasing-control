<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\DataProvider;

use Spryker\Client\Store\StoreClientInterface;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetSearchForm;

class BudgetSearchFormDataProvider
{
    public function __construct(protected readonly StoreClientInterface $storeClient)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $currencyIsoCodes = $this->storeClient->getCurrentStore()->getAvailableCurrencyIsoCodes();

        return [
            BudgetSearchForm::OPTION_CURRENCY_CHOICES => array_combine($currencyIsoCodes, $currencyIsoCodes),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return [];
    }
}

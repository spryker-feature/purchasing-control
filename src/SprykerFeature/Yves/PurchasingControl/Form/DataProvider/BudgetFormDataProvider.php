<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\DataProvider;

use Spryker\Client\Store\StoreClientInterface;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetForm;

class BudgetFormDataProvider
{
    public function __construct(protected readonly StoreClientInterface $storeClient)
    {
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getOptions(): array
    {
        return [
            BudgetForm::OPTION_CURRENCY_CHOICES => $this->buildCurrencyChoices(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function buildCurrencyChoices(): array
    {
        $choices = [];

        foreach ($this->storeClient->getCurrentStore()->getAvailableCurrencyIsoCodes() as $currencyIsoCode) {
            $choices[$currencyIsoCode] = $currencyIsoCode;
        }

        return $choices;
    }
}

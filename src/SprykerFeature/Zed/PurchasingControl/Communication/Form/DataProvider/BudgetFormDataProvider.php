<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider;

use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\BudgetForm;

class BudgetFormDataProvider
{
    public function __construct(protected CurrencyFacadeInterface $currencyFacade)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            BudgetForm::OPTION_CURRENCY_CHOICES => $this->getCurrencyChoices(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getCurrencyChoices(): array
    {
        $currencyChoices = [];
        foreach ($this->currencyFacade->getAllStoresWithCurrencies() as $storeWithCurrency) {
            foreach ($storeWithCurrency->getCurrencies() as $currencyTransfer) {
                $code = $currencyTransfer->getCodeOrFail();
                $currencyChoices[$code] = $code;
            }
        }

        ksort($currencyChoices);

        return $currencyChoices;
    }
}

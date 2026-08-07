<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Formatter;

use Generated\Shared\Transfer\BudgetSummaryTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyTransfer;
use NumberFormatter;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Client\Locale\LocaleClientInterface;
use Spryker\Client\Money\MoneyClientInterface;

class BudgetSummaryFormatter implements BudgetSummaryFormatterInterface
{
    protected const int PERCENT_MAX = 100;

    protected const int MILLION_IN_MINOR_UNITS = 100000000;

    protected const int MILLION_ABBREVIATION_PRECISION = 2;

    protected const string MILLION_ABBREVIATION_SUFFIX = 'M';

    protected const string GLOSSARY_KEY_BUDGET_USAGE = 'purchasing_control.recurring_order.budget.usage';

    protected const string GLOSSARY_PARAMETER_USED = '%used%';

    protected const string GLOSSARY_PARAMETER_TOTAL = '%total%';

    public function __construct(
        protected readonly MoneyClientInterface $moneyClient,
        protected readonly GlossaryStorageClientInterface $glossaryStorageClient,
        protected readonly LocaleClientInterface $localeClient,
    ) {
    }

    public function formatBudgetSummary(BudgetTransfer $budgetTransfer, string $currencyIsoCode): BudgetSummaryTransfer
    {
        $totalAmount = $budgetTransfer->getAmount() ?? 0;
        $usedAmount = $budgetTransfer->getConsumedAmount() ?? 0;
        $remainingAmount = $budgetTransfer->getRemainingAmount() ?? 0;

        $totalFormatted = $this->formatMoney($totalAmount, $currencyIsoCode);
        $usedFormatted = $this->formatMoney($usedAmount, $currencyIsoCode);

        return (new BudgetSummaryTransfer())
            ->setTotalAmount($totalAmount)
            ->setUsedAmount($usedAmount)
            ->setUsedPercent($this->calculateUsedPercent($totalAmount, $usedAmount))
            ->setTotalFormatted($totalFormatted)
            ->setUsedFormatted($usedFormatted)
            ->setRemainingFormatted($this->formatMoney($remainingAmount, $currencyIsoCode))
            ->setUsageInfo($this->formatUsageInfo($usedFormatted, $totalFormatted));
    }

    protected function calculateUsedPercent(int $totalAmount, int $usedAmount): int
    {
        if ($totalAmount <= 0) {
            return 0;
        }

        return min(static::PERCENT_MAX, (int)round($usedAmount / $totalAmount * static::PERCENT_MAX));
    }

    protected function formatUsageInfo(string $usedFormatted, string $totalFormatted): string
    {
        return $this->glossaryStorageClient->translate(
            static::GLOSSARY_KEY_BUDGET_USAGE,
            $this->localeClient->getCurrentLocale(),
            [
                static::GLOSSARY_PARAMETER_USED => $usedFormatted,
                static::GLOSSARY_PARAMETER_TOTAL => $totalFormatted,
            ],
        );
    }

    protected function formatMoney(int $amount, string $currencyIsoCode): string
    {
        if (abs($amount) >= static::MILLION_IN_MINOR_UNITS) {
            return $this->formatMillions($amount, $currencyIsoCode);
        }

        $moneyTransfer = (new MoneyTransfer())
            ->setAmount((string)$amount)
            ->setCurrency((new CurrencyTransfer())->setCode($currencyIsoCode));

        return $this->moneyClient->formatWithSymbol($moneyTransfer);
    }

    protected function formatMillions(int $amount, string $currencyIsoCode): string
    {
        $millions = round($amount / static::MILLION_IN_MINOR_UNITS, static::MILLION_ABBREVIATION_PRECISION);

        $formattedNumber = rtrim(
            rtrim(number_format($millions, static::MILLION_ABBREVIATION_PRECISION, '.', ''), '0'),
            '.',
        );

        return sprintf(
            '%s%s%s',
            $this->getCurrencySymbol($currencyIsoCode),
            $formattedNumber,
            static::MILLION_ABBREVIATION_SUFFIX,
        );
    }

    protected function getCurrencySymbol(string $currencyIsoCode): string
    {
        $numberFormatter = new NumberFormatter(
            sprintf('%s@currency=%s', $this->localeClient->getCurrentLocale(), $currencyIsoCode),
            NumberFormatter::CURRENCY,
        );

        $currencySymbol = $numberFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        return $currencySymbol !== false ? $currencySymbol : $currencyIsoCode;
    }
}

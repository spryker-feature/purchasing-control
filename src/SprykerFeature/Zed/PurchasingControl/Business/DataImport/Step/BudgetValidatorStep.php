<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use DateTime;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\DataImport\Business\Exception\DataImportException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\BudgetDataSetInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;

class BudgetValidatorStep implements DataImportStepInterface
{
    /**
     * @var array<string, bool>
     */
    protected array $currencyExistsCache = [];

    public function __construct(
        protected PurchasingControlConfig $config,
        protected CurrencyFacadeInterface $currencyFacade,
    ) {
    }

    public function execute(DataSetInterface $dataSet): void
    {
        $this->validateName($dataSet);
        $this->validateAmount($dataSet);
        $this->validateCurrency($dataSet);
        $this->validateDateRange($dataSet);
        $this->validateEnforcementRule($dataSet);
    }

    protected function validateName(DataSetInterface $dataSet): void
    {
        if (trim($dataSet[BudgetDataSetInterface::COLUMN_NAME]) === '') {
            throw new DataImportException('Budget name must not be empty.');
        }
    }

    protected function validateAmount(DataSetInterface $dataSet): void
    {
        if ((int)$dataSet[BudgetDataSetInterface::COLUMN_AMOUNT] <= 0) {
            throw new DataImportException(
                sprintf('Budget amount must be greater than 0, "%s" given.', $dataSet[BudgetDataSetInterface::COLUMN_AMOUNT]),
            );
        }
    }

    protected function validateCurrency(DataSetInterface $dataSet): void
    {
        $currencyIsoCode = $dataSet[BudgetDataSetInterface::COLUMN_CURRENCY_ISO_CODE];

        if (!isset($this->currencyExistsCache[$currencyIsoCode])) {
            $this->currencyExistsCache[$currencyIsoCode] = $this->currencyFacade->findCurrencyByIsoCode($currencyIsoCode) !== null;
        }

        if (!$this->currencyExistsCache[$currencyIsoCode]) {
            throw new DataImportException(
                sprintf('Currency with ISO code "%s" does not exist in the system.', $currencyIsoCode),
            );
        }
    }

    protected function validateDateRange(DataSetInterface $dataSet): void
    {
        $startsAt = new DateTime($dataSet[BudgetDataSetInterface::COLUMN_STARTS_AT]);
        $endsAt = new DateTime($dataSet[BudgetDataSetInterface::COLUMN_ENDS_AT]);

        if ($startsAt >= $endsAt) {
            throw new DataImportException(
                sprintf(
                    'Budget starts_at "%s" must be before ends_at "%s".',
                    $dataSet[BudgetDataSetInterface::COLUMN_STARTS_AT],
                    $dataSet[BudgetDataSetInterface::COLUMN_ENDS_AT],
                ),
            );
        }
    }

    protected function validateEnforcementRule(DataSetInterface $dataSet): void
    {
        $enforcementRule = $dataSet[BudgetDataSetInterface::COLUMN_ENFORCEMENT_RULE];

        if (!in_array($enforcementRule, $this->config->getAllowedBudgetEnforcementRules(), true)) {
            throw new DataImportException(
                sprintf(
                    'Invalid enforcement rule "%s". Allowed values are: %s.',
                    $enforcementRule,
                    implode(', ', $this->config->getAllowedBudgetEnforcementRules()),
                ),
            );
        }
    }
}

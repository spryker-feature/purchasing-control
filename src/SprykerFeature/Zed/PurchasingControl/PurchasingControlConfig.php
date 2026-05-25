<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl;

use Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig as SharedPurchasingControlConfig;

class PurchasingControlConfig extends AbstractBundleConfig
{
    public const string IMPORT_TYPE_COST_CENTER = 'purchasing-control-cost-center';

    public const string IMPORT_TYPE_BUDGET = 'purchasing-control-budget';

    public const string IMPORT_TYPE_COST_CENTER_TO_COMPANY_BUSINESS_UNIT = 'purchasing-control-cost-center-to-company-business-unit';

    protected const int DEFAULT_BUSINESS_UNIT_SELECT_LIMIT = 100;

    protected const int DEFAULT_COST_CENTER_FILTER_LIMIT = 100;

    protected const int DEFAULT_BUDGET_FILTER_LIMIT = 100;

    protected const string MODULE_NAME = 'PurchasingControl';

    protected const string IMPORT_FILE_NAME_COST_CENTER = 'cost_center.csv';

    protected const string IMPORT_FILE_NAME_BUDGET = 'budget.csv';

    protected const string IMPORT_FILE_NAME_COST_CENTER_TO_COMPANY_BUSINESS_UNIT = 'cost_center_company_business_unit.csv';

    protected const string IMPORT_DIRECTORY = '/data/import/common/common/';

    /**
     * @var list<string>
     */
    protected const array OMS_ITEM_STATE_NAMES_FOR_BUDGET_RELEASE = ['cancelled'];

    /**
     * @var list<string>
     */
    protected const array OMS_ITEM_STATE_NAMES_FOR_REFUND_BUDGET_RELEASE = ['refunded', 'refund pending'];

    protected const bool REFUND_WITH_SHIPMENT_ENABLED = false;

    /**
     * Specification:
     * - Returns the data importer configuration for cost centers.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer
     */
    public function getCostCenterDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_COST_CENTER)
            ->setFileName(static::IMPORT_FILE_NAME_COST_CENTER)
            ->setModuleName(static::MODULE_NAME)
            ->setDirectory(static::IMPORT_DIRECTORY);
    }

    /**
     * Specification:
     * - Returns the data importer configuration for budgets.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer
     */
    public function getBudgetDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_BUDGET)
            ->setFileName(static::IMPORT_FILE_NAME_BUDGET)
            ->setModuleName(static::MODULE_NAME)
            ->setDirectory(static::IMPORT_DIRECTORY);
    }

    /**
     * Specification:
     * - Returns the data importer configuration for cost center to company business unit relations.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer
     */
    public function getCostCenterToCompanyBusinessUnitDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_COST_CENTER_TO_COMPANY_BUSINESS_UNIT)
            ->setFileName(static::IMPORT_FILE_NAME_COST_CENTER_TO_COMPANY_BUSINESS_UNIT)
            ->setModuleName(static::MODULE_NAME)
            ->setDirectory(static::IMPORT_DIRECTORY);
    }

    /**
     * Specification:
     * - Returns the list of allowed enforcement rule values for budget import validation.
     *
     * @api
     *
     * @return list<string>
     */
    public function getAllowedBudgetEnforcementRules(): array
    {
        return [
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN,
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL,
        ];
    }

    /**
     * Specification:
     * - Returns the maximum number of business units loaded into the cost center form dropdown.
     * - Projects with large BU datasets should override this method or switch to async autocomplete.
     *
     * @api
     */
    public function getBusinessUnitSelectLimit(): int
    {
        return static::DEFAULT_BUSINESS_UNIT_SELECT_LIMIT;
    }

    /**
     * Specification:
     * - Returns the maximum number of cost centers loaded into the Back Office orders list filter dropdown.
     *
     * @api
     */
    public function getCostCenterFilterLimit(): int
    {
        return static::DEFAULT_COST_CENTER_FILTER_LIMIT;
    }

    /**
     * Specification:
     * - Returns the maximum number of budgets loaded into the Back Office orders list filter dropdown.
     *
     * @api
     */
    public function getBudgetFilterLimit(): int
    {
        return static::DEFAULT_BUDGET_FILTER_LIMIT;
    }

    /**
     * Specification:
     * - Returns OMS item state names for which budget should be released on the cancellation.
     * - Items already in one of these states are treated as previously canceled when resolving partial budget release for an order.
     *
     * @api
     *
     * @return list<string>
     */
    public function getOmsItemStateNamesForCancellationBudgetRelease(): array
    {
        return static::OMS_ITEM_STATE_NAMES_FOR_BUDGET_RELEASE;
    }

    /**
     * Specification:
     * - Returns OMS item state names for which budget should be released on refund.
     * - Items already in one of these states are treated as previously refunded when resolving whether a shipment group is fully refunded.
     *
     * @api
     *
     * @return list<string>
     */
    public function getOmsItemStateNamesForRefundBudgetRelease(): array
    {
        return static::OMS_ITEM_STATE_NAMES_FOR_REFUND_BUDGET_RELEASE;
    }

    /**
     * Specification:
     * - Returns true if shipment cost should be included in the budget release on refund.
     * - When enabled, the shipment group expense refundable amount is released if all items in the group are refunded.
     *
     * @api
     */
    public function isRefundWithShipmentEnabled(): bool
    {
        return static::REFUND_WITH_SHIPMENT_ENABLED;
    }
}

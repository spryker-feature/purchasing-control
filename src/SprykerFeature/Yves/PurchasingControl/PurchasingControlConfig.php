<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl;

use Spryker\Yves\Kernel\AbstractBundleConfig;

class PurchasingControlConfig extends AbstractBundleConfig
{
    protected const int COMPANY_BUSINESS_UNIT_LIMIT_FOR_COST_CENTER_FORM = 30;

    protected const int COST_CENTER_LIST_DEFAULT_ITEMS_PER_PAGE = 10;

    protected const int BUDGET_LIST_DEFAULT_ITEMS_PER_PAGE = 10;

    protected const int DEFAULT_SUMMARY_COST_CENTER_LIMIT = 3;

    protected const int DEFAULT_SUMMARY_BUDGET_LIMIT = 5;

    /**
     * Specification:
     * - Returns the maximum number of company business units loaded into the cost center form dropdown.
     *
     * @api
     */
    public function getCompanyBusinessUnitLimitForCostCenterForm(): int
    {
        return static::COMPANY_BUSINESS_UNIT_LIMIT_FOR_COST_CENTER_FORM;
    }

    /**
     * Specification:
     * - Returns the default number of cost center list items displayed per page on the storefront.
     *
     * @api
     */
    public function getCostCenterListDefaultItemsPerPage(): int
    {
        return static::COST_CENTER_LIST_DEFAULT_ITEMS_PER_PAGE;
    }

    /**
     * Specification:
     * - Returns the default number of budget list items displayed per page on the storefront.
     *
     * @api
     */
    public function getBudgetListDefaultItemsPerPage(): int
    {
        return static::BUDGET_LIST_DEFAULT_ITEMS_PER_PAGE;
    }

    /**
     * Specification:
     * - Returns the maximum number of cost centers displayed in the checkout summary cost center selector.
     *
     * @api
     */
    public function getSummaryCostCenterLimit(): int
    {
        return static::DEFAULT_SUMMARY_COST_CENTER_LIMIT;
    }

    /**
     * Specification:
     * - Returns the maximum number of budgets displayed in the checkout summary budget selector for a selected cost center.
     *
     * @api
     */
    public function getSummaryBudgetLimit(): int
    {
        return static::DEFAULT_SUMMARY_BUDGET_LIMIT;
    }
}

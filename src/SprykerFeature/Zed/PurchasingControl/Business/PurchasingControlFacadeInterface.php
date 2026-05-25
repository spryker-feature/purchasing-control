<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetCollectionResponseTransfer;
use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;

interface PurchasingControlFacadeInterface
{
    /**
     * Specification:
     * - Creates cost centers from the collection.
     * - Validates that all `CostCenterTransfer.companyBusinessUnitIds` belong to `CostCenterCollectionRequestTransfer.customer.companyUserTransfer.fkCompany` when the customer is provided.
     * - Skips ownership validation when `CostCenterCollectionRequestTransfer.customer` is not set.
     * - Validates that `CostCenterTransfer.name` is not empty.
     * - Validates that `CostCenterTransfer.companyBusinessUnitIds` is not empty.
     * - Adds an error to the response for each invalid item.
     * - Persists no items and returns early when `CostCenterCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `CostCenterCollectionRequestTransfer.isTransactional` is `false`.
     * - Returns all items from the request alongside any validation errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CostCenterCollectionResponseTransfer
     */
    public function createCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates cost centers from the collection by their IDs.
     * - Validates that the cost center identified by `CostCenterTransfer.idCostCenter` belongs to `CostCenterCollectionRequestTransfer.customer.companyUserTransfer.fkCompany` when the customer is provided.
     * - Validates that all `CostCenterTransfer.companyBusinessUnitIds` belong to `CostCenterCollectionRequestTransfer.customer.companyUserTransfer.fkCompany` when the customer is provided.
     * - Skips ownership validation when `CostCenterCollectionRequestTransfer.customer` is not set.
     * - Validates that `CostCenterTransfer.name` is not empty.
     * - Validates that `CostCenterTransfer.companyBusinessUnitIds` is not empty.
     * - Adds an error to the response for each invalid item.
     * - Persists no items and returns early when `CostCenterCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `CostCenterCollectionRequestTransfer.isTransactional` is `false`.
     * - Deactivates all budgets belonging to a cost center when `CostCenterTransfer.isActive` is set to `false`.
     * - Returns all items from the request alongside any validation errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CostCenterCollectionResponseTransfer
     */
    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * Specification:
     * - Returns a collection of cost centers filtered by `CostCenterCriteriaTransfer.costCenterConditions`.
     * - Filters by `CostCenterConditionsTransfer.costCenterIds` when provided.
     * - Filters by `CostCenterConditionsTransfer.companyBusinessUnitIds` by business unit IDs when provided.
     * - Filters by `CostCenterConditionsTransfer.isActive` cost centers and budgets when provided.
     * - Filters by `CostCenterConditionsTransfer.currencyIsoCodes` when provided returns only cost centers that have at least one budget matching the currency.
     * - Filters by `CostCenterConditionsTransfer.budgetActiveOnDate` returns budgets valid for the provided date (starts_at ≤ date ≤ ends_at).
     * - Populates `CostCenterTransfer.budgets` with budgets when `CostCenterConditionsTransfer.withBudgets` is true.
     * - Supports sorting via `CostCenterCriteriaTransfer.sortCollection`.
     * - Supports pagination via `CostCenterCriteriaTransfer.pagination`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CostCenterCriteriaTransfer $costCenterCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CostCenterCollectionTransfer
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * Specification:
     * - Creates budgets from the collection.
     * - Validates that `BudgetTransfer.amount` is greater than zero.
     * - Validates that `BudgetTransfer.currencyIsoCode` is a 3-character ISO code.
     * - Validates that `BudgetTransfer.startsAt` is before `BudgetTransfer.endsAt` when both are provided.
     * - Adds an error to the response for each invalid item.
     * - Persists no items and returns early when `BudgetCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `BudgetCollectionRequestTransfer.isTransactional` is `false`.
     * - Returns all items from the request alongside any validation errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\BudgetCollectionResponseTransfer
     */
    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;

    /**
     * Specification:
     * - Updates budgets from the collection by their IDs.
     * - Validates that `BudgetTransfer.amount` is greater than zero.
     * - Validates that `BudgetTransfer.currencyIsoCode` is a 3-character ISO code.
     * - Validates that `BudgetTransfer.startsAt` is before `BudgetTransfer.endsAt` when both are provided.
     * - Adds an error to the response for each invalid item.
     * - Persists no items and returns early when `BudgetCollectionRequestTransfer.isTransactional` is `true` and any item fails validation.
     * - Persists valid items and skips invalid ones when `BudgetCollectionRequestTransfer.isTransactional` is `false`.
     * - Returns all items from the request alongside any validation errors.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\BudgetCollectionResponseTransfer
     */
    public function updateBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;

    /**
     * Specification:
     * - Returns a collection of budgets filtered by `BudgetCriteriaTransfer.budgetConditions`.
     * - Filters by `BudgetConditionsTransfer.budgetIds` when provided.
     * - Filters by `BudgetConditionsTransfer.costCenterIds` when provided.
     * - Filters by `BudgetConditionsTransfer.currencyIsoCodes` when provided.
     * - Filters by `BudgetConditionsTransfer.isActive` when provided.
     * - Filters by `BudgetConditionsTransfer.activeOnDate` — only budgets where starts_at ≤ date ≤ ends_at.
     * - Includes consumed and remaining amounts calculated from budget consumption records.
     * - Supports sorting via `BudgetCriteriaTransfer.sortCollection`.
     * - Supports pagination via `BudgetCriteriaTransfer.pagination`.
     *
     * @api
     */
    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;

    /**
     * Specification:
     * - Requires `CostCenterQuoteUpdateRequestTransfer.idQuote`.
     * - Requires `CostCenterQuoteUpdateRequestTransfer.idCostCenter`.
     * - Requires `CostCenterQuoteUpdateRequestTransfer.customer`.
     * - Loads the quote by `CostCenterQuoteUpdateRequestTransfer.idQuote`.
     * - Returns an error when the quote is not found.
     * - Returns an error when any of `QuoteTransfer.quoteApprovals` has status `approved` — budget changes are forbidden after approval.
     * - Returns an error when `CostCenterQuoteUpdateRequestTransfer.customer.companyUserTransfer` is not provided.
     * - Validates the cost center is active and belongs to the company business unit from `CostCenterQuoteUpdateRequestTransfer.customer.companyUserTransfer`.
     * - Returns an error when the cost center does not belong to the business unit.
     * - Validates the budget belongs to the cost center when `CostCenterQuoteUpdateRequestTransfer.idBudget` is provided.
     * - Returns an error when the budget does not belong to the cost center.
     * - Returns the updated quote on success with `isSuccessful=true`.
     * - Maps QuoteResponseTransfer errors to `CostCenterQuoteUpdateResponseTransfer.errors`.
     *
     * @api
     */
    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl;

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

interface PurchasingControlClientInterface
{
    /**
     * Specification:
     * - Makes a Zed call to fetch cost centers matching `CostCenterCriteriaTransfer.costCenterConditions`.
     * - Filters by `CostCenterConditionsTransfer.costCenterIds` when provided.
     * - Filters by `CostCenterConditionsTransfer.companyBusinessUnitIds` by company business unit when provided.
     * - Filters by `CostCenterConditionsTransfer.isActive` when provided.
     * - Filters by `CostCenterConditionsTransfer.currencyIsoCodes` when provided — only returns cost centers with at least one active budget matching the currency and valid today's date range.
     * - Populates `CostCenterTransfer.budgets` with active budgets valid today (filtered by currency when provided) when `CostCenterConditionsTransfer.withBudgets` is true.
     * - Supports sorting via `CostCenterCriteriaTransfer.sortCollection`.
     * - Supports pagination via `CostCenterCriteriaTransfer.pagination`.
     *
     * @api
     *
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getCostCenterCollectionAction()
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * Specification:
     * - Makes a Zed call to fetch budgets matching `BudgetCriteriaTransfer.budgetConditions`.
     * - Filters by `BudgetConditionsTransfer.budgetIds` when provided.
     * - Filters by `BudgetConditionsTransfer.costCenterIds` when provided.
     * - Filters by `BudgetConditionsTransfer.currencyIsoCodes` when provided.
     * - Filters by `BudgetConditionsTransfer.isActive` when provided.
     * - Filters by `BudgetConditionsTransfer.activeOnDate` — only budgets where starts_at ≤ date ≤ ends_at.
     * - Includes consumed and remaining amounts.
     * - Supports sorting via `BudgetCriteriaTransfer.sortCollection`.
     * - Supports pagination via `BudgetCriteriaTransfer.pagination`.
     *
     * @api
     *
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::getBudgetCollectionAction()
     */
    public function getBudgetCollection(BudgetCriteriaTransfer $budgetCriteriaTransfer): BudgetCollectionTransfer;

    /**
     * Specification:
     * - Makes a Zed call to create cost centers.
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
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::createCostCenterCollectionAction()
     */
    public function createCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * Specification:
     * - Makes a Zed call to update cost centers.
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
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateCostCenterCollectionAction()
     */
    public function updateCostCenterCollection(CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer): CostCenterCollectionResponseTransfer;

    /**
     * Specification:
     * - Makes a Zed call to validate and persist cost center and budget on the quote.
     * - Requires `CostCenterQuoteUpdateRequestTransfer.idQuote`.
     * - Requires `CostCenterQuoteUpdateRequestTransfer.idCostCenter`.
     * - Requires `CostCenterQuoteUpdateRequestTransfer.customer`.
     * - Returns an error when `CostCenterQuoteUpdateRequestTransfer.customer.companyUserTransfer` is not provided.
     * - Validates the cost center is active and belongs to the company business unit from `CostCenterQuoteUpdateRequestTransfer.customer.companyUserTransfer`.
     * - Returns an error when the cost center does not belong to the business unit.
     * - Validates the budget belongs to the cost center when `CostCenterQuoteUpdateRequestTransfer.idBudget` is provided.
     * - Returns an error when the budget does not belong to the cost center.
     * - Sets `idCostCenter` and `idBudget` on the quote and persists it.
     * - Returns the updated quote on success with `isSuccessful=true`.
     * - Returns `isSuccessful=false` with errors when validation or persistence fails.
     *
     * @api
     *
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateQuoteCostCenterAction()
     */
    public function updateQuoteCostCenter(CostCenterQuoteUpdateRequestTransfer $requestTransfer): CostCenterQuoteUpdateResponseTransfer;

    /**
     * Specification:
     * - Makes a Zed call to create budgets.
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
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::createBudgetCollectionAction()
     */
    public function createBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;

    /**
     * Specification:
     * - Makes a Zed call to update budgets.
     * - Updates budgets from the collection by their `BudgetTransfer.idBudget`.
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
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\GatewayController::updateBudgetCollectionAction()
     */
    public function updateBudgetCollection(BudgetCollectionRequestTransfer $budgetCollectionRequestTransfer): BudgetCollectionResponseTransfer;
}

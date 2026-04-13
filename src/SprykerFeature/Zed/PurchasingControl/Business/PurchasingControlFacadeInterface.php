<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetResponseTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterResponseTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;

interface PurchasingControlFacadeInterface
{
    /**
     * Specification:
     * - Creates a new cost center linked to the given business unit.
     * - Validates that name is non-empty; returns errors on CostCenterResponseTransfer on failure.
     *
     * @api
     */
    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer;

    /**
     * Specification:
     * - Updates an existing cost center by ID.
     * - Validates that name is non-empty; returns errors on CostCenterResponseTransfer on failure.
     *
     * @api
     */
    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer;

    /**
     * Specification:
     * - Returns a collection of cost centers filtered by the given criteria.
     * - Supports filtering by business unit and active status.
     * - Supports pagination via PaginationTransfer.
     *
     * @api
     */
    public function getCostCenterCollection(CostCenterCriteriaTransfer $costCenterCriteriaTransfer): CostCenterCollectionTransfer;

    /**
     * Specification:
     * - Creates a new budget linked to the given cost center.
     * - Validates: starts_at < ends_at, amount > 0, currency is 3-char ISO code.
     * - Returns errors on BudgetResponseTransfer on failure.
     *
     * @api
     */
    public function createBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer;

    /**
     * Specification:
     * - Updates an existing budget by ID.
     * - Validates: starts_at < ends_at, amount > 0, currency is 3-char ISO code.
     * - Returns errors on BudgetResponseTransfer on failure.
     *
     * @api
     */
    public function updateBudget(BudgetTransfer $budgetTransfer): BudgetResponseTransfer;

    /**
     * Specification:
     * - Returns active budgets for the given cost center filtered by currency.
     * - Filters: is_active=true, starts_at ≤ today ≤ ends_at, currency matches.
     * - Includes consumed and remaining amounts calculated from budget consumption records.
     *
     * @api
     */
    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer;

    /**
     * Specification:
     * - Returns a cost center by its ID.
     * - Returns an empty CostCenterTransfer when not found.
     *
     * @api
     */
    public function getCostCenterById(int $idCostCenter): CostCenterTransfer;

    /**
     * Specification:
     * - Returns a budget by its ID.
     * - Returns an empty BudgetTransfer when not found.
     *
     * @api
     */
    public function getBudgetById(int $idBudget): BudgetTransfer;

    /**
     * Specification:
     * - Copies fk_cost_center and fk_budget from the quote to the sales order.
     * - Does nothing when no cost center is selected on the quote.
     *
     * @api
     */
    public function saveCostCenterToOrder(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;

    /**
     * Specification:
     * - Sets idCostCenter on the quote when the customer's business unit has exactly one active cost center and none is selected yet.
     * - Sets idBudget on the quote to the first active budget for the selected cost center when none is selected yet.
     * - Does nothing for non-company users or when selections are already set.
     *
     * @api
     */
    public function expandQuoteWithDefaultCostCenter(QuoteTransfer $quoteTransfer): QuoteTransfer;

    /**
     * Specification:
     * - Returns true when no budget is selected on the quote.
     * - Returns true when the quote grand total is within the remaining budget.
     * - Returns true with a warning error when the enforcement rule is 'warn' and the budget is exceeded.
     * - Returns true when rule is 'require_approval', budget is exceeded, and the quote has been approved.
     * - Returns false with a checkout error when rule is 'block' and the budget is exceeded.
     * - Returns false with a checkout error when rule is 'require_approval', budget is exceeded, and the quote is not yet approved.
     *
     * @api
     */
    public function validateBudgetForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool;

    /**
     * Specification:
     * - Creates a budget consumption record linking the given budget to the sales order.
     * - Records the consumed amount in cents.
     *
     * @api
     */
    public function consumeBudget(int $idBudget, int $idSalesOrder, int $amountInCents): void;

    /**
     * Specification:
     * - Deletes all budget consumption records for the given sales order.
     * - Restores the consumed budget balance by removing the consumption records.
     *
     * @api
     */
    public function restoreBudget(int $idSalesOrder): void;
}

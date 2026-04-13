<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;

interface PurchasingControlClientInterface
{
    /**
     * Specification:
     * - Returns active cost centers linked to the given company business unit.
     * - Filters: is_active=true, cost center is linked to the given business unit.
     * - Filters: cost center has at least one active budget matching the given currency and today's date.
     *
     * @api
     */
    public function getActiveCostCentersForCompanyBusinessUnit(int $idCompanyBusinessUnit, string $currencyIsoCode): CostCenterCollectionTransfer;

    /**
     * Specification:
     * - Returns active budgets for the given cost center filtered by currency.
     * - Filters: is_active=true, starts_at <= today <= ends_at, currency matches.
     * - Includes consumed and remaining amounts.
     *
     * @api
     */
    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer;
}

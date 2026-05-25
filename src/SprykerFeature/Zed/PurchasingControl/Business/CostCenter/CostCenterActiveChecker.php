<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use ArrayObject;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterActiveChecker implements CostCenterActiveCheckerInterface
{
    /**
     * @var array<string, \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer>>
     */
    protected array $activeCostCentersCache = [];

    public function __construct(
        protected readonly PurchasingControlRepositoryInterface $purchasingControlRepository,
    ) {
    }

    public function isCostCenterActiveForQuote(BudgetTransfer $budgetTransfer, QuoteTransfer $quoteTransfer): bool
    {
        $activeCostCenters = $this->findActiveCostCentersForQuote($quoteTransfer);
        if ($activeCostCenters === null) {
            return true;
        }

        foreach ($activeCostCenters as $costCenter) {
            if ($costCenter->getIdCostCenter() === $budgetTransfer->getIdCostCenter()) {
                return true;
            }
        }

        return false;
    }

    public function hasActiveCostCentersForQuote(QuoteTransfer $quoteTransfer): bool
    {
        $activeCostCenters = $this->findActiveCostCentersForQuote($quoteTransfer);

        return $activeCostCenters !== null && $activeCostCenters->count() > 0;
    }

    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer>|null
     */
    protected function findActiveCostCentersForQuote(QuoteTransfer $quoteTransfer): ?ArrayObject
    {
        $idCompanyBusinessUnit = $quoteTransfer->getCustomer()
            ?->getCompanyUserTransfer()
            ?->getFkCompanyBusinessUnit();

        if ($idCompanyBusinessUnit === null) {
            return null;
        }

        $currencyIsoCode = $quoteTransfer->getCurrency()?->getCode() ?? '';
        $cacheKey = sprintf('%d:%s', $idCompanyBusinessUnit, $currencyIsoCode);

        if (!isset($this->activeCostCentersCache[$cacheKey])) {
            $this->activeCostCentersCache[$cacheKey] = $this->purchasingControlRepository
                ->getCostCenterCollection(
                    (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                        (new CostCenterConditionsTransfer())
                            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
                            ->setIsActive(true)
                            ->addCurrencyIsoCode($currencyIsoCode),
                    ),
                )
                ->getCostCenters();
        }

        return $this->activeCostCentersCache[$cacheKey];
    }
}

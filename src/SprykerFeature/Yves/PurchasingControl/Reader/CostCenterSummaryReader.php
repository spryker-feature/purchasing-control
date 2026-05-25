<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Yves\Kernel\PermissionAwareTrait;
use SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig;

class CostCenterSummaryReader implements CostCenterSummaryReaderInterface
{
    use PermissionAwareTrait;

    /**
     * @uses \Spryker\Client\CompanySalesConnector\Plugin\Permission\SeeCompanyOrdersPermissionPlugin::KEY
     */
    protected const string PERMISSION_SEE_COMPANY_ORDERS = 'SeeCompanyOrdersPermissionPlugin';

    protected ?CostCenterCollectionTransfer $costCenterCollection = null;

    public function __construct(
        protected readonly CustomerClientInterface $customerClient,
        protected readonly CostCenterReaderInterface $costCenterReader,
        protected readonly PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    public function getActiveCostCenterCount(): int
    {
        $count = 0;

        foreach ($this->getCostCenterCollection()->getCostCenters() as $costCenterTransfer) {
            if ($costCenterTransfer->getIsActive() === true) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<string, array{totalAmount: int, totalConsumedAmount: int, totalRemainingAmount: int}>
     */
    public function getBudgetSummaries(): array
    {
        $summaries = [];

        foreach ($this->getCostCenterCollection()->getCostCenters() as $costCenterTransfer) {
            if ($costCenterTransfer->getIsActive() !== true) {
                continue;
            }

            foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
                $currency = $budgetTransfer->getCurrencyIsoCodeOrFail();

                if (!isset($summaries[$currency])) {
                    $summaries[$currency] = [
                        'totalAmount' => 0,
                        'totalConsumedAmount' => 0,
                        'totalRemainingAmount' => 0,
                    ];
                }

                $summaries[$currency]['totalAmount'] += $budgetTransfer->getAmount() ?? 0;
                $summaries[$currency]['totalConsumedAmount'] += $budgetTransfer->getConsumedAmount() ?? 0;
                $summaries[$currency]['totalRemainingAmount'] += $budgetTransfer->getRemainingAmount() ?? 0;
            }
        }

        return $summaries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCostCenterBudgetDetails(): array
    {
        $details = [];
        $costCenterLimit = $this->purchasingControlConfig->getSummaryCostCenterLimit();
        $budgetLimit = $this->purchasingControlConfig->getSummaryBudgetLimit();

        foreach ($this->getCostCenterCollection()->getCostCenters() as $costCenterTransfer) {
            if (count($details) >= $costCenterLimit) {
                break;
            }

            if ($costCenterTransfer->getIsActive() !== true) {
                continue;
            }

            $budgets = [];

            foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
                if (count($budgets) >= $budgetLimit) {
                    break;
                }

                $budgets[] = [
                    'name' => $budgetTransfer->getNameOrFail(),
                    'amount' => $budgetTransfer->getAmount() ?? 0,
                    'consumedAmount' => $budgetTransfer->getConsumedAmount() ?? 0,
                    'remainingAmount' => $budgetTransfer->getRemainingAmount() ?? 0,
                    'currency' => $budgetTransfer->getCurrencyIsoCodeOrFail(),
                ];
            }

            $details[] = [
                'name' => $costCenterTransfer->getNameOrFail(),
                'budgets' => $budgets,
            ];
        }

        return $details;
    }

    protected function getCostCenterCollection(): CostCenterCollectionTransfer
    {
        if ($this->costCenterCollection === null) {
            $this->costCenterCollection = $this->resolveCostCenterCollection();
        }

        return $this->costCenterCollection;
    }

    protected function resolveCostCenterCollection(): CostCenterCollectionTransfer
    {
        $companyUserTransfer = $this->customerClient->getCustomer()?->getCompanyUserTransfer();

        if ($companyUserTransfer === null) {
            return new CostCenterCollectionTransfer();
        }

        if ($this->can(static::PERMISSION_SEE_COMPANY_ORDERS, $companyUserTransfer->getIdCompanyUser())) {
            $idCompany = $companyUserTransfer->getFkCompany() ?: null;

            if ($idCompany !== null) {
                return $this->costCenterReader->getCostCentersWithBudgetsForOrderSearch(null, $idCompany);
            }
        }

        $idCompanyBusinessUnit = $companyUserTransfer->getFkCompanyBusinessUnit() ?: null;

        if ($idCompanyBusinessUnit === null) {
            return new CostCenterCollectionTransfer();
        }

        return $this->costCenterReader->getCostCentersWithBudgetsForOrderSearch($idCompanyBusinessUnit);
    }
}

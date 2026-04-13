<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Client\PurchasingControl;

use Generated\Shared\Transfer\BudgetCollectionTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \SprykerFeature\Client\PurchasingControl\PurchasingControlFactory getFactory()
 */
class PurchasingControlClient extends AbstractClient implements PurchasingControlClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getActiveCostCentersForCompanyBusinessUnit(int $idCompanyBusinessUnit, string $currencyIsoCode): CostCenterCollectionTransfer
    {
        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->setCurrencyIsoCode($currencyIsoCode);

        return $this->getFactory()
            ->createPurchasingControlStub()
            ->getActiveCostCentersForCompanyBusinessUnit($criteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getActiveBudgetsForCostCenter(int $idCostCenter, string $currencyIsoCode): BudgetCollectionTransfer
    {
        $criteriaTransfer = (new BudgetCriteriaTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setCurrencyIsoCode($currencyIsoCode)
            ->setIsActive(true)
            ->setActiveOnDate(date('Y-m-d'));

        return $this->getFactory()
            ->createPurchasingControlStub()
            ->getActiveBudgetsForCostCenter($criteriaTransfer);
    }
}

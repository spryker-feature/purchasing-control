<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Reader;

use DateTime;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface;

class CostCenterReader implements CostCenterReaderInterface
{
    public function __construct(protected readonly PurchasingControlClientInterface $purchasingControlClient)
    {
    }

    public function getActiveCostCentersForCompanyBusinessUnit(
        int $idCompanyBusinessUnit,
        string $currencyIsoCode,
        bool $isLocked = false,
    ): CostCenterCollectionTransfer {
        $conditionsTransfer = (new CostCenterConditionsTransfer())
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive(true)
            ->addCurrencyIsoCode($currencyIsoCode)
            ->setWithBudgets(true);

        if (!$isLocked) {
            $conditionsTransfer->setBudgetActiveOnDate((new DateTime())->format('Y-m-d'));
        }

        return $this->purchasingControlClient->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions($conditionsTransfer),
        );
    }

    public function getCostCentersWithBudgetsForOrderSearch(?int $idCompanyBusinessUnit, ?int $idCompany = null): CostCenterCollectionTransfer
    {
        $conditions = (new CostCenterConditionsTransfer())
            ->setWithBudgets(true)
            ->setWithInactiveBudgets(true);

        if ($idCompanyBusinessUnit !== null) {
            $conditions->addIdCompanyBusinessUnit($idCompanyBusinessUnit);
        }

        if ($idCompany !== null) {
            $conditions->addIdCompany($idCompany);
        }

        $criteriaTransfer = (new CostCenterCriteriaTransfer())->setCostCenterConditions($conditions);

        return $this->purchasingControlClient->getCostCenterCollection($criteriaTransfer);
    }

    public function findCostCenter(string $costCenterUuid, int $idCompany): ?CostCenterTransfer
    {
        $criteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addUuid($costCenterUuid)
                    ->addIdCompany($idCompany),
            );

        $costCenterCollectionTransfer = $this->purchasingControlClient->getCostCenterCollection($criteriaTransfer);

        return $costCenterCollectionTransfer->getCostCenters()->getIterator()->current() ?: null;
    }
}

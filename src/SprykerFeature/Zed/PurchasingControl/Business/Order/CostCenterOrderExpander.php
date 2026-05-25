<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Order;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;

class CostCenterOrderExpander implements CostCenterOrderExpanderInterface
{
    public function __construct(
        protected readonly CostCenterReaderInterface $costCenterReader,
        protected readonly BudgetReaderInterface $budgetReader,
        protected readonly CompanyFacadeInterface $companyFacade,
    ) {
    }

    public function expandOrder(OrderTransfer $orderTransfer): OrderTransfer
    {
        $orderTransfer = $this->expandOrderWithCompany($orderTransfer);

        $idCostCenter = $orderTransfer->getFkCostCenter();
        if ($idCostCenter === null) {
            return $orderTransfer;
        }

        $costCenterCollectionTransfer = $this->costCenterReader->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())
                ->setCostCenterConditions(
                    (new CostCenterConditionsTransfer())
                        ->addIdCostCenter($idCostCenter)
                        ->setWithBudgets(false),
                ),
        );

        $costCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->offsetGet(0);
        if ($costCenterTransfer !== null) {
            $costCenterTransfer->setCompany($orderTransfer->getCompanyName());
        }

        $orderTransfer->setCostCenter($costCenterTransfer);
        $idBudget = $orderTransfer->getFkBudget();
        if ($idBudget === null) {
            return $orderTransfer;
        }

        $budgetCollectionTransfer = $this->budgetReader->getBudgetCollection(
            (new BudgetCriteriaTransfer())
                ->setBudgetConditions(
                    (new BudgetConditionsTransfer())
                        ->addIdBudget($idBudget)
                        ->setWithBudgetConsumption(false),
                ),
        );
        $orderTransfer->setBudget($budgetCollectionTransfer->getBudgets()->offsetGet(0));

        return $orderTransfer;
    }

    protected function expandOrderWithCompany(OrderTransfer $orderTransfer): OrderTransfer
    {
        $companyUuid = $orderTransfer->getCompanyUuid();
        if ($companyUuid === null) {
            return $orderTransfer;
        }

        $companyResponseTransfer = $this->companyFacade->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($companyUuid),
        );

        $companyName = $companyResponseTransfer->getCompanyTransfer()?->getName();
        if ($companyName === null) {
            return $orderTransfer;
        }

        return $orderTransfer->setCompanyName($companyName);
    }
}

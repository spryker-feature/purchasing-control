<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Budget;

use Generated\Shared\Transfer\BudgetConsumptionTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class BudgetConsumptionWriter implements BudgetConsumptionWriterInterface
{
    public function __construct(protected readonly PurchasingControlEntityManagerInterface $entityManager)
    {
    }

    public function consumeBudget(int $idBudget, int $idSalesOrder, int $amountInCents): void
    {
        $budgetConsumptionTransfer = (new BudgetConsumptionTransfer())
            ->setIdBudget($idBudget)
            ->setIdSalesOrder($idSalesOrder)
            ->setAmount($amountInCents);

        $this->entityManager->createBudgetConsumption($budgetConsumptionTransfer);
    }

    public function restoreBudget(int $idSalesOrder): void
    {
        $this->entityManager->deleteBudgetConsumptionByIdSalesOrder($idSalesOrder);
    }
}

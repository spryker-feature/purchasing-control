<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\BudgetDataSetInterface;

class BudgetWriterStep implements DataImportStepInterface
{
    public function execute(DataSetInterface $dataSet): void
    {
        $idCostCenter = $dataSet[BudgetDataSetInterface::KEY_ID_COST_CENTER];

        $budgetEntity = SpyBudgetQuery::create()
            ->filterByFkCostCenter($idCostCenter)
            ->filterByName($dataSet[BudgetDataSetInterface::COLUMN_NAME])
            ->findOneOrCreate();

        $budgetEntity
            ->setFkCostCenter($idCostCenter)
            ->setName($dataSet[BudgetDataSetInterface::COLUMN_NAME])
            ->setAmount((string)$dataSet[BudgetDataSetInterface::COLUMN_AMOUNT])
            ->setCurrencyIsoCode($dataSet[BudgetDataSetInterface::COLUMN_CURRENCY_ISO_CODE])
            ->setStartsAt($dataSet[BudgetDataSetInterface::COLUMN_STARTS_AT])
            ->setEndsAt($dataSet[BudgetDataSetInterface::COLUMN_ENDS_AT])
            ->setEnforcementRule($dataSet[BudgetDataSetInterface::COLUMN_ENFORCEMENT_RULE])
            ->setIsActive((bool)$dataSet[BudgetDataSetInterface::COLUMN_IS_ACTIVE]);

        if ($budgetEntity->isNew() || $budgetEntity->isModified()) {
            $budgetEntity->save();
        }
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\CostCenterDataSetInterface;

class CostCenterWriterStep implements DataImportStepInterface
{
    public function execute(DataSetInterface $dataSet): void
    {
        $costCenterEntity = SpyCostCenterQuery::create()
            ->filterByUuid($dataSet[CostCenterDataSetInterface::COLUMN_KEY])
            ->findOneOrCreate();

        $costCenterEntity
            ->setName($dataSet[CostCenterDataSetInterface::COLUMN_NAME])
            ->setDescription($dataSet[CostCenterDataSetInterface::COLUMN_DESCRIPTION] ?: null)
            ->setIsActive((bool)$dataSet[CostCenterDataSetInterface::COLUMN_IS_ACTIVE])
            ->setUuid($dataSet[CostCenterDataSetInterface::COLUMN_KEY]);

        if ($costCenterEntity->isNew() || $costCenterEntity->isModified()) {
            $costCenterEntity->save();
        }
    }
}

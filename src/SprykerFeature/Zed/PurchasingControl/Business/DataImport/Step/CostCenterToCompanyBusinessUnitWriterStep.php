<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnitQuery;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\CostCenterToCompanyBusinessUnitDataSetInterface;

class CostCenterToCompanyBusinessUnitWriterStep implements DataImportStepInterface
{
    public function execute(DataSetInterface $dataSet): void
    {
        $costCenterToCompanyBusinessUnitEntity = SpyCostCenterToCompanyBusinessUnitQuery::create()
            ->filterByFkCostCenter($dataSet[CostCenterToCompanyBusinessUnitDataSetInterface::KEY_ID_COST_CENTER])
            ->filterByFkCompanyBusinessUnit($dataSet[CostCenterToCompanyBusinessUnitDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT])
            ->findOneOrCreate();

        if ($costCenterToCompanyBusinessUnitEntity->isNew()) {
            $costCenterToCompanyBusinessUnitEntity->save();
        }
    }
}

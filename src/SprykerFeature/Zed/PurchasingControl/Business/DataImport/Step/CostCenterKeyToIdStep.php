<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\BudgetDataSetInterface;

class CostCenterKeyToIdStep implements DataImportStepInterface
{
    protected const int CACHE_SIZE = 1000;

    /**
     * @var array<string, int>
     */
    protected array $idCostCenterCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $costCenterKey = $dataSet[BudgetDataSetInterface::COLUMN_COST_CENTER_KEY];

        if (isset($this->idCostCenterCache[$costCenterKey])) {
            $dataSet[BudgetDataSetInterface::KEY_ID_COST_CENTER] = $this->idCostCenterCache[$costCenterKey];

            return;
        }

        $costCenterEntity = SpyCostCenterQuery::create()
            ->filterByUuid($costCenterKey)
            ->findOne();

        if ($costCenterEntity === null) {
            throw new EntityNotFoundException(
                sprintf('Cost center with key "%s" not found.', $costCenterKey),
            );
        }

        $this->clearCacheIfLimitReached();

        $this->idCostCenterCache[$costCenterKey] = (int)$costCenterEntity->getIdCostCenter();
        $dataSet[BudgetDataSetInterface::KEY_ID_COST_CENTER] = $this->idCostCenterCache[$costCenterKey];
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCostCenterCache) >= static::CACHE_SIZE) {
            $this->idCostCenterCache = [];
        }
    }
}

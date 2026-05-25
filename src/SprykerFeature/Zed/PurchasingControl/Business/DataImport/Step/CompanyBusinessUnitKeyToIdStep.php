<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step;

use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\DataSet\CostCenterToCompanyBusinessUnitDataSetInterface;

class CompanyBusinessUnitKeyToIdStep implements DataImportStepInterface
{
    protected const int CACHE_SIZE = 1000;

    /**
     * @var array<string, int>
     */
    protected array $idCompanyBusinessUnitCache = [];

    public function execute(DataSetInterface $dataSet): void
    {
        $businessUnitKey = $dataSet[CostCenterToCompanyBusinessUnitDataSetInterface::COLUMN_BUSINESS_UNIT_KEY];

        if (isset($this->idCompanyBusinessUnitCache[$businessUnitKey])) {
            $dataSet[CostCenterToCompanyBusinessUnitDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT] = $this->idCompanyBusinessUnitCache[$businessUnitKey];

            return;
        }

        $companyBusinessUnitEntity = SpyCompanyBusinessUnitQuery::create()
            ->filterByKey($businessUnitKey)
            ->findOne();

        if ($companyBusinessUnitEntity === null) {
            throw new EntityNotFoundException(
                sprintf('Company business unit with key "%s" not found.', $businessUnitKey),
            );
        }

        $this->clearCacheIfLimitReached();

        $this->idCompanyBusinessUnitCache[$businessUnitKey] = (int)$companyBusinessUnitEntity->getIdCompanyBusinessUnit();
        $dataSet[CostCenterToCompanyBusinessUnitDataSetInterface::KEY_ID_COMPANY_BUSINESS_UNIT] = $this->idCompanyBusinessUnitCache[$businessUnitKey];
    }

    protected function clearCacheIfLimitReached(): void
    {
        if (count($this->idCompanyBusinessUnitCache) >= static::CACHE_SIZE) {
            $this->idCompanyBusinessUnitCache = [];
        }
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Persistence;

use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumptionQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnitQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use SprykerFeature\Zed\PurchasingControl\Persistence\Propel\Mapper\PurchasingControlMapper;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlDependencyProvider;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface getEntityManager()
 */
class PurchasingControlPersistenceFactory extends AbstractPersistenceFactory
{
    public function createCostCenterQuery(): SpyCostCenterQuery
    {
        return SpyCostCenterQuery::create();
    }

    public function createCostCenterToCompanyBusinessUnitQuery(): SpyCostCenterToCompanyBusinessUnitQuery
    {
        return SpyCostCenterToCompanyBusinessUnitQuery::create();
    }

    public function createBudgetQuery(): SpyBudgetQuery
    {
        return SpyBudgetQuery::create();
    }

    public function createBudgetConsumptionQuery(): SpyBudgetConsumptionQuery
    {
        return SpyBudgetConsumptionQuery::create();
    }

    public function createPurchasingControlMapper(): PurchasingControlMapper
    {
        return new PurchasingControlMapper();
    }

    /**
     * @module CompanyBusinessUnit
     */
    public function getCompanyBusinessUnitQuery(): SpyCompanyBusinessUnitQuery
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::PROPEL_QUERY_COMPANY_BUSINESS_UNIT);
    }
}

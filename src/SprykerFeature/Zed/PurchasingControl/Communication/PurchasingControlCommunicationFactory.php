<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication;

use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\BudgetForm;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\CostCenterForm;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider\BudgetFormDataProvider;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider\CostCenterFormDataProvider;
use SprykerFeature\Zed\PurchasingControl\Communication\Table\BudgetTable;
use SprykerFeature\Zed\PurchasingControl\Communication\Table\CostCenterTable;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlDependencyProvider;
use Symfony\Component\Form\FormInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface getEntityManager()
 */
class PurchasingControlCommunicationFactory extends AbstractCommunicationFactory
{
    public function createCostCenterTable(): CostCenterTable
    {
        return new CostCenterTable(
            SpyCostCenterQuery::create(),
            SpyBudgetQuery::create(),
            $this->getUtilDateTimeService(),
        );
    }

    public function createBudgetTable(int $idCostCenter): BudgetTable
    {
        return new BudgetTable(
            SpyBudgetQuery::create(),
            $idCostCenter,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCostCenterForm(array $data = []): FormInterface
    {
        return $this->getFormFactory()->create(
            CostCenterForm::class,
            $data,
            $this->createCostCenterFormDataProvider()->getOptions(),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createBudgetForm(array $data = []): FormInterface
    {
        return $this->getFormFactory()->create(
            BudgetForm::class,
            $data,
            $this->createBudgetFormDataProvider()->getOptions(),
        );
    }

    public function createCostCenterFormDataProvider(): CostCenterFormDataProvider
    {
        return new CostCenterFormDataProvider(
            $this->getCompanyBusinessUnitFacade(),
            $this->getConfig(),
        );
    }

    public function createBudgetFormDataProvider(): BudgetFormDataProvider
    {
        return new BudgetFormDataProvider($this->getCurrencyFacade());
    }

    public function getCurrencyFacade(): CurrencyFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_CURRENCY);
    }

    public function getCompanyBusinessUnitFacade(): CompanyBusinessUnitFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_COMPANY_BUSINESS_UNIT);
    }

    public function getUtilDateTimeService(): UtilDateTimeServiceInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::SERVICE_UTIL_DATE_TIME);
    }
}

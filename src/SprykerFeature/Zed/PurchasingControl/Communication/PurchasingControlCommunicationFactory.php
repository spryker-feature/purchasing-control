<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication;

use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\Glossary\Business\GlossaryFacadeInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use Spryker\Zed\Money\Business\MoneyFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\BudgetForm;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\Builder\OrdersTableFilterFormBuilder;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\Builder\OrdersTableFilterFormBuilderInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\CostCenterForm;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider\BudgetFormDataProvider;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider\CostCenterFormDataProvider;
use SprykerFeature\Zed\PurchasingControl\Communication\Reader\CostCenterReader;
use SprykerFeature\Zed\PurchasingControl\Communication\Reader\CostCenterReaderInterface;
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
            $this->getMoneyFacade(),
            $this->getUtilDateTimeService(),
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createCostCenterForm(CostCenterTransfer $costCenterTransfer, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(CostCenterForm::class, $costCenterTransfer, $options);
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
        return new CostCenterFormDataProvider($this->getCompanyBusinessUnitFacade());
    }

    public function createBudgetFormDataProvider(): BudgetFormDataProvider
    {
        return new BudgetFormDataProvider($this->getCurrencyFacade());
    }

    public function createOrdersTableFilterFormBuilder(): OrdersTableFilterFormBuilderInterface
    {
        return new OrdersTableFilterFormBuilder($this->getFacade(), $this->getConfig());
    }

    public function createCostCenterReader(): CostCenterReaderInterface
    {
        return new CostCenterReader($this->getFacade());
    }

    public function getCurrencyFacade(): CurrencyFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_CURRENCY);
    }

    public function getGlossaryFacade(): GlossaryFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_GLOSSARY);
    }

    public function getLocaleFacade(): LocaleFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_LOCALE);
    }

    public function getCompanyBusinessUnitFacade(): CompanyBusinessUnitFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_COMPANY_BUSINESS_UNIT);
    }

    public function getMoneyFacade(): MoneyFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_MONEY);
    }

    public function getUtilDateTimeService(): UtilDateTimeServiceInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::SERVICE_UTIL_DATE_TIME);
    }
}

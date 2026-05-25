<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\CompanyBusinessUnit\CompanyBusinessUnitClientInterface;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Client\Money\MoneyClientInterface;
use Spryker\Client\Quote\QuoteClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Yves\Kernel\AbstractFactory;
use SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface;
use SprykerFeature\Yves\PurchasingControl\Expander\CostCenterOrderSearchFormExpander;
use SprykerFeature\Yves\PurchasingControl\Expander\CostCenterOrderSearchFormExpanderInterface;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetForm;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetSearchForm;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterForm;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSearchForm;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\BudgetFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\BudgetSearchFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterSearchFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterSelectorFormDataProvider;
use SprykerFeature\Yves\PurchasingControl\Form\Handler\BudgetSearchFormHandler;
use SprykerFeature\Yves\PurchasingControl\Form\Handler\CostCenterOrderSearchFormHandler;
use SprykerFeature\Yves\PurchasingControl\Form\Handler\CostCenterOrderSearchFormHandlerInterface;
use SprykerFeature\Yves\PurchasingControl\Form\Handler\CostCenterSearchFormHandler;
use SprykerFeature\Yves\PurchasingControl\Reader\BudgetReader;
use SprykerFeature\Yves\PurchasingControl\Reader\BudgetReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReader;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterSummaryReader;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterSummaryReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Resolver\BudgetResolver;
use SprykerFeature\Yves\PurchasingControl\Resolver\BudgetResolverInterface;
use SprykerFeature\Yves\PurchasingControl\Resolver\CostCenterResolver;
use SprykerFeature\Yves\PurchasingControl\Resolver\CostCenterResolverInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Client\PurchasingControl\PurchasingControlClientInterface getClient()
 */
class PurchasingControlFactory extends AbstractFactory
{
    public function createCostCenterSelectorForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $dataProvider = $this->createCostCenterSelectorFormDataProvider();
        $dataAndOptions = $dataProvider->getDataAndOptions($quoteTransfer);

        return $this->getFormFactory()->create(
            CostCenterSelectorForm::class,
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_DATA],
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_OPTIONS],
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    public function createCostCenterSelectorFormFromDataAndOptions(array $data, array $options): FormInterface
    {
        return $this->getFormFactory()->create(CostCenterSelectorForm::class, $data, $options);
    }

    public function createCostCenterSelectorFormDataProvider(): CostCenterSelectorFormDataProvider
    {
        return new CostCenterSelectorFormDataProvider(
            $this->createCostCenterResolver(),
            $this->createBudgetResolver(),
            $this->getMoneyClient(),
        );
    }

    public function getFormFactory(): FormFactory
    {
        return $this->getProvidedDependency(ApplicationConstants::FORM_FACTORY);
    }

    public function createCostCenterResolver(): CostCenterResolverInterface
    {
        return new CostCenterResolver(
            $this->getCustomerClient(),
            $this->createCostCenterReader(),
        );
    }

    public function createBudgetResolver(): BudgetResolverInterface
    {
        return new BudgetResolver();
    }

    public function createCostCenterReader(): CostCenterReaderInterface
    {
        return new CostCenterReader($this->getPurchasingControlClient());
    }

    public function createCostCenterSummaryReader(): CostCenterSummaryReaderInterface
    {
        return new CostCenterSummaryReader(
            $this->getCustomerClient(),
            $this->createCostCenterReader(),
            $this->getConfig(),
        );
    }

    public function getPurchasingControlClient(): PurchasingControlClientInterface
    {
        return $this->getClient();
    }

    public function getMoneyClient(): MoneyClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_MONEY);
    }

    public function getCustomerClient(): CustomerClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_CUSTOMER);
    }

    public function getQuoteClient(): QuoteClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_QUOTE);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createCostCenterForm(?CostCenterTransfer $costCenterTransfer = null, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(CostCenterForm::class, $costCenterTransfer, $options);
    }

    public function createCostCenterFormDataProvider(): CostCenterFormDataProvider
    {
        return new CostCenterFormDataProvider(
            $this->getCompanyBusinessUnitClient(),
            $this->getConfig(),
        );
    }

    public function getCompanyBusinessUnitClient(): CompanyBusinessUnitClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_COMPANY_BUSINESS_UNIT);
    }

    public function createCostCenterSearchForm(int $idCompany): FormInterface
    {
        $dataProvider = $this->createCostCenterSearchFormDataProvider();

        return $this->getFormFactory()->create(
            CostCenterSearchForm::class,
            $dataProvider->getData(),
            $dataProvider->getOptions($idCompany),
        );
    }

    public function createCostCenterSearchFormDataProvider(): CostCenterSearchFormDataProvider
    {
        return new CostCenterSearchFormDataProvider(
            $this->getCompanyBusinessUnitClient(),
            $this->getConfig(),
        );
    }

    public function createCostCenterSearchFormHandler(): CostCenterSearchFormHandler
    {
        return new CostCenterSearchFormHandler();
    }

    public function createBudgetSearchForm(string $costCenterUuid): FormInterface
    {
        $dataProvider = $this->createBudgetSearchFormDataProvider();

        return $this->getFormFactory()->create(
            BudgetSearchForm::class,
            array_merge($dataProvider->getData(), [BudgetSearchForm::FIELD_COST_CENTER_UUID => $costCenterUuid]),
            array_merge($dataProvider->getOptions(), [BudgetSearchForm::OPTION_COST_CENTER_UUID => $costCenterUuid]),
        );
    }

    public function createBudgetSearchFormDataProvider(): BudgetSearchFormDataProvider
    {
        return new BudgetSearchFormDataProvider($this->getStoreClient());
    }

    public function createBudgetSearchFormHandler(): BudgetSearchFormHandler
    {
        return new BudgetSearchFormHandler();
    }

    public function createBudgetReader(): BudgetReaderInterface
    {
        return new BudgetReader($this->getPurchasingControlClient(), $this->createCostCenterReader());
    }

    public function createBudgetFormDataProvider(): BudgetFormDataProvider
    {
        return new BudgetFormDataProvider($this->getStoreClient());
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createBudgetForm(?BudgetTransfer $budgetTransfer = null, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create(BudgetForm::class, $budgetTransfer, $options);
    }

    public function getStoreClient(): StoreClientInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::CLIENT_STORE);
    }

    public function createCostCenterOrderSearchFormExpander(): CostCenterOrderSearchFormExpanderInterface
    {
        return new CostCenterOrderSearchFormExpander(
            $this->getCustomerClient(),
            $this->createCostCenterReader(),
        );
    }

    public function createCostCenterOrderSearchFormHandler(): CostCenterOrderSearchFormHandlerInterface
    {
        return new CostCenterOrderSearchFormHandler();
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\DataImport\Business\DataImportFactoryTrait;
use Spryker\Zed\DataImport\Business\Model\DataImporterInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Quote\Business\QuoteFacadeInterface;
use Spryker\Zed\QuoteApproval\Business\QuoteApprovalFacadeInterface;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCancellationRestorer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCancellationRestorerInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCheckoutValidator;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCheckoutValidatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumerInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionApplier;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionApplierInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionReader;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCreator;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetCreatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReader;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetRefundRestorer;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetRefundRestorerInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetUpdater;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetUpdaterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\OrderItemExtractor;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\OrderItemExtractorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\OrderItemStateChecker;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\OrderItemStateCheckerInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\ShipmentExpenseCalculator;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\ShipmentExpenseCalculatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterActiveChecker;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterActiveCheckerInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterBudgetExpander;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterBudgetExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterCreator;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterCreatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReader;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterUpdater;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterUpdaterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\BudgetValidatorStep;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\BudgetWriterStep;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\CompanyBusinessUnitKeyToIdStep;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\CostCenterKeyToIdStep;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\CostCenterToCompanyBusinessUnitWriterStep;
use SprykerFeature\Zed\PurchasingControl\Business\DataImport\Step\CostCenterWriterStep;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderExpander;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderListExpander;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderListExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderSaver;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderSaverInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Order\OrderSearchQueryExpander;
use SprykerFeature\Zed\PurchasingControl\Business\Order\OrderSearchQueryExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteExpander;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdater;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdaterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidator;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteUpdateValidatorInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlDependencyProvider;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface getRepository()
 */
class PurchasingControlBusinessFactory extends AbstractBusinessFactory
{
    use DataImportFactoryTrait;

    public function getCostCenterDataImporter(?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null): DataImporterInterface
    {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface $dataImporter */
        $dataImporter = $this->getDataImporter(
            $this->getConfig()->getCostCenterDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createTransactionAwareDataSetStepBroker();
        $dataSetStepBroker->addStep($this->createCostCenterWriterStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);

        return $dataImporter;
    }

    public function getBudgetDataImporter(?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null): DataImporterInterface
    {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface $dataImporter */
        $dataImporter = $this->getDataImporter(
            $this->getConfig()->getBudgetDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createTransactionAwareDataSetStepBroker();
        $dataSetStepBroker
            ->addStep($this->createCostCenterKeyToIdStep())
            ->addStep($this->createBudgetValidatorStep())
            ->addStep($this->createBudgetWriterStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);

        return $dataImporter;
    }

    public function getDataImporter(
        DataImporterDataSourceConfigurationTransfer $dataImporterDataSourceConfigurationTransfer,
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null,
    ): DataImporterInterface {
        if ($dataImporterConfigurationTransfer) {
            return $this->getDataImportFactory()->getCsvDataImporterFromConfig($dataImporterConfigurationTransfer);
        }

        return $this->getCsvDataImporterFromConfig($dataImporterDataSourceConfigurationTransfer);
    }

    public function getCostCenterToCompanyBusinessUnitDataImporter(
        ?DataImporterConfigurationTransfer $dataImporterConfigurationTransfer = null
    ): DataImporterInterface {
        /** @var \Spryker\Zed\DataImport\Business\Model\DataImporterInterface&\Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerAwareInterface $dataImporter */
        $dataImporter = $this->getDataImporter(
            $this->getConfig()->getCostCenterToCompanyBusinessUnitDataImporterConfiguration(),
            $dataImporterConfigurationTransfer,
        );

        /** @var \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetStepBrokerInterface&\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepAwareInterface $dataSetStepBroker */
        $dataSetStepBroker = $this->createTransactionAwareDataSetStepBroker();
        $dataSetStepBroker
            ->addStep($this->createCostCenterKeyToIdStep())
            ->addStep($this->createCompanyBusinessUnitKeyToIdStep())
            ->addStep($this->createCostCenterToCompanyBusinessUnitWriterStep());

        $dataImporter->addDataSetStepBroker($dataSetStepBroker);

        return $dataImporter;
    }

    public function createCostCenterWriterStep(): DataImportStepInterface
    {
        return new CostCenterWriterStep();
    }

    public function createCostCenterKeyToIdStep(): DataImportStepInterface
    {
        return new CostCenterKeyToIdStep();
    }

    public function createBudgetValidatorStep(): DataImportStepInterface
    {
        return new BudgetValidatorStep($this->getConfig(), $this->getCurrencyFacade());
    }

    public function getCurrencyFacade(): CurrencyFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_CURRENCY);
    }

    public function createBudgetWriterStep(): DataImportStepInterface
    {
        return new BudgetWriterStep();
    }

    public function createCompanyBusinessUnitKeyToIdStep(): DataImportStepInterface
    {
        return new CompanyBusinessUnitKeyToIdStep();
    }

    public function createCostCenterToCompanyBusinessUnitWriterStep(): DataImportStepInterface
    {
        return new CostCenterToCompanyBusinessUnitWriterStep();
    }

    public function createCostCenterCreator(): CostCenterCreatorInterface
    {
        return new CostCenterCreator($this->getEntityManager(), $this->createCostCenterValidator(), $this->createCollectionIndexOperations());
    }

    public function createCostCenterUpdater(): CostCenterUpdaterInterface
    {
        return new CostCenterUpdater($this->getEntityManager(), $this->createCostCenterValidator(), $this->createCollectionIndexOperations());
    }

    public function createCostCenterValidator(): CostCenterValidatorInterface
    {
        return new CostCenterValidator(
            $this->createCostCenterReader(),
            $this->getRepository(),
        );
    }

    public function createCollectionIndexOperations(): CollectionIndexOperations
    {
        return new CollectionIndexOperations();
    }

    public function createCostCenterReader(): CostCenterReaderInterface
    {
        return new CostCenterReader($this->getRepository(), $this->createCostCenterBudgetExpander());
    }

    public function createCostCenterBudgetExpander(): CostCenterBudgetExpanderInterface
    {
        return new CostCenterBudgetExpander($this->createBudgetReader());
    }

    public function createBudgetCreator(): BudgetCreatorInterface
    {
        return new BudgetCreator($this->getEntityManager(), $this->createBudgetValidator(), $this->createCollectionIndexOperations());
    }

    public function createBudgetUpdater(): BudgetUpdaterInterface
    {
        return new BudgetUpdater($this->getEntityManager(), $this->createBudgetValidator(), $this->createCollectionIndexOperations());
    }

    public function createBudgetConsumer(): BudgetConsumerInterface
    {
        return new BudgetConsumer($this->getEntityManager());
    }

    public function createBudgetCancellationRestorer(): BudgetCancellationRestorerInterface
    {
        return new BudgetCancellationRestorer(
            $this->getSalesFacade(),
            $this->createBudgetConsumptionReader(),
            $this->createBudgetConsumptionApplier(),
            $this->createOrderItemExtractor(),
            $this->createOrderItemStateChecker(),
            $this->createShipmentExpenseCalculator(),
        );
    }

    public function createBudgetRefundRestorer(): BudgetRefundRestorerInterface
    {
        return new BudgetRefundRestorer(
            $this->getSalesFacade(),
            $this->createBudgetConsumptionReader(),
            $this->createBudgetConsumptionApplier(),
            $this->createOrderItemExtractor(),
            $this->createShipmentExpenseCalculator(),
            $this->getConfig(),
        );
    }

    public function createBudgetConsumptionApplier(): BudgetConsumptionApplierInterface
    {
        return new BudgetConsumptionApplier($this->getEntityManager());
    }

    public function createOrderItemExtractor(): OrderItemExtractorInterface
    {
        return new OrderItemExtractor();
    }

    public function createOrderItemStateChecker(): OrderItemStateCheckerInterface
    {
        return new OrderItemStateChecker($this->getConfig());
    }

    public function createShipmentExpenseCalculator(): ShipmentExpenseCalculatorInterface
    {
        return new ShipmentExpenseCalculator($this->createOrderItemStateChecker());
    }

    public function createBudgetConsumptionReader(): BudgetConsumptionReaderInterface
    {
        return new BudgetConsumptionReader($this->getRepository());
    }

    public function createBudgetReader(): BudgetReaderInterface
    {
        return new BudgetReader($this->getRepository());
    }

    public function createCostCenterOrderExpander(): CostCenterOrderExpanderInterface
    {
        return new CostCenterOrderExpander(
            $this->createCostCenterReader(),
            $this->createBudgetReader(),
            $this->getCompanyFacade(),
        );
    }

    public function createCostCenterOrderListExpander(): CostCenterOrderListExpanderInterface
    {
        return new CostCenterOrderListExpander(
            $this->createCostCenterReader(),
            $this->createBudgetReader(),
        );
    }

    public function createCostCenterOrderSaver(): CostCenterOrderSaverInterface
    {
        return new CostCenterOrderSaver($this->getEntityManager());
    }

    public function createOrderSearchQueryExpander(): OrderSearchQueryExpanderInterface
    {
        return new OrderSearchQueryExpander();
    }

    public function createBudgetValidator(): BudgetValidatorInterface
    {
        return new BudgetValidator($this->getRepository());
    }

    public function createBudgetCheckoutValidator(): BudgetCheckoutValidatorInterface
    {
        return new BudgetCheckoutValidator(
            $this->getRepository(),
            $this->getQuoteApprovalFacade(),
            $this->createCostCenterActiveChecker(),
        );
    }

    public function createCostCenterActiveChecker(): CostCenterActiveCheckerInterface
    {
        return new CostCenterActiveChecker($this->getRepository());
    }

    public function createCostCenterQuoteExpander(): CostCenterQuoteExpanderInterface
    {
        return new CostCenterQuoteExpander($this->getRepository(), $this->getCompanyUserFacade());
    }

    public function createCostCenterQuoteUpdater(): CostCenterQuoteUpdaterInterface
    {
        return new CostCenterQuoteUpdater($this->getQuoteFacade(), $this->createCostCenterQuoteUpdateValidator());
    }

    public function createCostCenterQuoteUpdateValidator(): CostCenterQuoteUpdateValidatorInterface
    {
        return new CostCenterQuoteUpdateValidator($this->createCostCenterReader());
    }

    public function getQuoteFacade(): QuoteFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_QUOTE);
    }

    public function getQuoteApprovalFacade(): QuoteApprovalFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_QUOTE_APPROVAL);
    }

    public function getCompanyFacade(): CompanyFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_COMPANY);
    }

    public function getCompanyUserFacade(): CompanyUserFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_COMPANY_USER);
    }

    public function getSalesFacade(): SalesFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_SALES);
    }
}

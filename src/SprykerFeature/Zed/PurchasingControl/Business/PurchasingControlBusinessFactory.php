<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\QuoteApproval\Business\QuoteApprovalFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionWriter;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetConsumptionWriterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReader;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidatorInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetWriter;
use SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetWriterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReader;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterWriter;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterWriterInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderSaver;
use SprykerFeature\Zed\PurchasingControl\Business\Order\CostCenterOrderSaverInterface;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteExpander;
use SprykerFeature\Zed\PurchasingControl\Business\Quote\CostCenterQuoteExpanderInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlDependencyProvider;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface getRepository()
 */
class PurchasingControlBusinessFactory extends AbstractBusinessFactory
{
    public function createCostCenterWriter(): CostCenterWriterInterface
    {
        return new CostCenterWriter($this->getEntityManager());
    }

    public function createCostCenterReader(): CostCenterReaderInterface
    {
        return new CostCenterReader($this->getRepository());
    }

    public function createBudgetWriter(): BudgetWriterInterface
    {
        return new BudgetWriter($this->getEntityManager());
    }

    public function createBudgetConsumptionWriter(): BudgetConsumptionWriterInterface
    {
        return new BudgetConsumptionWriter($this->getEntityManager());
    }

    public function createBudgetReader(): BudgetReaderInterface
    {
        return new BudgetReader($this->getRepository());
    }

    public function createCostCenterOrderSaver(): CostCenterOrderSaverInterface
    {
        return new CostCenterOrderSaver($this->getEntityManager());
    }

    public function createBudgetValidator(): BudgetValidatorInterface
    {
        return new BudgetValidator(
            $this->getRepository(),
            $this->getQuoteApprovalFacade(),
        );
    }

    public function createCostCenterQuoteExpander(): CostCenterQuoteExpanderInterface
    {
        return new CostCenterQuoteExpander($this->getRepository());
    }

    public function getQuoteApprovalFacade(): QuoteApprovalFacadeInterface
    {
        return $this->getProvidedDependency(PurchasingControlDependencyProvider::FACADE_QUOTE_APPROVAL);
    }
}

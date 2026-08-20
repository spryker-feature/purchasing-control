<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterSelectorFormDataProvider;
use Symfony\Component\Form\FormView;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
abstract class AbstractQuoteRequestCostCenterSelectorWidget extends AbstractWidget
{
    protected const string PARAMETER_FORM = 'form';

    protected const string PARAMETER_SELECTED_COST_CENTER = 'selectedCostCenter';

    protected const string PARAMETER_SELECTED_BUDGET = 'selectedBudget';

    protected const string PARAMETER_QUOTE = 'quote';

    protected const string PARAMETER_QUOTE_REQUEST_REFERENCE = 'quoteRequestReference';

    protected const string PARAMETER_IS_LOCKED = 'isLocked';

    protected const string PARAMETER_FORM_ACTION_ROUTE = 'formActionRoute';

    abstract protected function isQuoteRequestEditable(QuoteRequestTransfer $quoteRequestTransfer): bool;

    abstract protected function getDefaultFormActionRoute(): string;

    public function __construct(QuoteRequestTransfer $quoteRequestTransfer, ?string $formActionRoute = null)
    {
        $this->addParameter(static::PARAMETER_FORM_ACTION_ROUTE, $formActionRoute ?? $this->getDefaultFormActionRoute());
        $this->addParameter(static::PARAMETER_QUOTE_REQUEST_REFERENCE, $quoteRequestTransfer->getQuoteRequestReference());
        $this->addParameter(static::PARAMETER_IS_LOCKED, !$this->isQuoteRequestEditable($quoteRequestTransfer));

        $quoteTransfer = $quoteRequestTransfer->getLatestVersion()?->getQuote();
        // Cost centers are scoped per business unit, so the owner of the quote request decides which ones apply.
        $idCompanyBusinessUnit = $quoteRequestTransfer->getCompanyUser()?->getFkCompanyBusinessUnit();

        if ($quoteTransfer === null || $idCompanyBusinessUnit === null) {
            $this->addEmptyParameters();

            return;
        }

        $this->addSelectorParameters($quoteTransfer, $idCompanyBusinessUnit);
    }

    protected function addSelectorParameters(QuoteTransfer $quoteTransfer, int $idCompanyBusinessUnit): void
    {
        $dataProvider = $this->getFactory()->createCostCenterSelectorFormDataProvider();

        /** @var array{data: array<string, mixed>, options: array<string, mixed>, selectedCostCenter: \Generated\Shared\Transfer\CostCenterTransfer|null, selectedBudget: \Generated\Shared\Transfer\BudgetTransfer|null} $dataAndOptions */
        $dataAndOptions = $dataProvider->getDataAndOptions($quoteTransfer, $idCompanyBusinessUnit);

        $form = $this->getFactory()->createCostCenterSelectorFormFromDataAndOptions(
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_DATA],
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_OPTIONS],
        );

        $this->addFormParameter($form->createView());
        $this->addSelectedCostCenterParameter($dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_COST_CENTER]);
        $this->addSelectedBudgetParameter($dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_BUDGET]);
        $this->addQuoteParameter($quoteTransfer);
    }

    protected function addEmptyParameters(): void
    {
        $this->addParameter(static::PARAMETER_FORM, null);
        $this->addParameter(static::PARAMETER_SELECTED_COST_CENTER, null);
        $this->addParameter(static::PARAMETER_SELECTED_BUDGET, null);
        $this->addParameter(static::PARAMETER_QUOTE, null);
    }

    protected function addFormParameter(FormView $form): void
    {
        $this->addParameter(static::PARAMETER_FORM, $form);
    }

    protected function addSelectedCostCenterParameter(?CostCenterTransfer $selectedCostCenterTransfer): void
    {
        $this->addParameter(static::PARAMETER_SELECTED_COST_CENTER, $selectedCostCenterTransfer);
    }

    protected function addSelectedBudgetParameter(?BudgetTransfer $selectedBudgetTransfer): void
    {
        $this->addParameter(static::PARAMETER_SELECTED_BUDGET, $selectedBudgetTransfer);
    }

    protected function addQuoteParameter(QuoteTransfer $quoteTransfer): void
    {
        $this->addParameter(static::PARAMETER_QUOTE, $quoteTransfer);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;
use SprykerFeature\Yves\PurchasingControl\Form\DataProvider\CostCenterSelectorFormDataProvider;
use Symfony\Component\Form\FormView;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterSelectorWidget extends AbstractWidget
{
    protected const string PARAMETER_FORM = 'form';

    protected const string PARAMETER_IS_LOCKED = 'isLocked';

    protected const string PARAMETER_SELECTED_COST_CENTER = 'selectedCostCenter';

    protected const string PARAMETER_SELECTED_BUDGET = 'selectedBudget';

    protected const string PARAMETER_QUOTE = 'quote';

    public function __construct(QuoteTransfer $quoteTransfer)
    {
        $dataProvider = $this->getFactory()->createCostCenterSelectorFormDataProvider();

        /** @var array{data: array<string, mixed>, options: array<string, mixed>, selectedCostCenter: \Generated\Shared\Transfer\CostCenterTransfer|null, selectedBudget: \Generated\Shared\Transfer\BudgetTransfer|null} $dataAndOptions */
        $dataAndOptions = $dataProvider->getDataAndOptions($quoteTransfer);

        $form = $this->getFactory()->createCostCenterSelectorFormFromDataAndOptions(
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_DATA],
            $dataAndOptions[CostCenterSelectorFormDataProvider::KEY_OPTIONS],
        );

        $this->addFormParameter($form->createView());
        $this->addIsLockedParameter($quoteTransfer);
        $this->addSelectedCostCenterParameter($dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_COST_CENTER]);
        $this->addSelectedBudgetParameter($dataAndOptions[CostCenterSelectorFormDataProvider::KEY_SELECTED_BUDGET]);
        $this->addQuoteParameter($quoteTransfer);
    }

    public static function getName(): string
    {
        return 'CostCenterSelectorWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/views/cost-center-selector/cost-center-selector.twig';
    }

    protected function addFormParameter(FormView $form): void
    {
        $this->addParameter(static::PARAMETER_FORM, $form);
    }

    protected function addIsLockedParameter(QuoteTransfer $quoteTransfer): void
    {
        $this->addParameter(static::PARAMETER_IS_LOCKED, $this->isQuoteInApprovalProcess($quoteTransfer));
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

    protected function isQuoteInApprovalProcess(QuoteTransfer $quoteTransfer): bool
    {
        return $quoteTransfer->getIsLocked() === true;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form\Builder;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdersTableFilterFormBuilder implements OrdersTableFilterFormBuilderInterface
{
    protected const string OPTION_COST_CENTERS = 'cost_centers';

    protected const string OPTION_BUDGETS = 'budgets';

    protected const string FIELD_COST_CENTER_IDS = 'costCenterIds';

    protected const string FIELD_BUDGET_IDS = 'budgetIds';

    protected const string LABEL_COST_CENTERS = 'Cost Center';

    protected const string LABEL_BUDGETS = 'Budget';

    protected const string PLACEHOLDER_COST_CENTERS = 'Select Cost Centers';

    protected const string PLACEHOLDER_BUDGETS = 'Select Budget';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetSuggestController::indexAction()
     */
    protected const string ROUTE_BUDGET_SUGGEST = '/purchasing-control/budget-suggest/index';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetSuggestController::PARAM_COST_CENTER_IDS
     */
    protected const string PARAM_COST_CENTER_IDS = 'idsCostCenter';

    public function __construct(
        protected PurchasingControlFacadeInterface $purchasingControlFacade,
        protected PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    public function expandConfigureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            static::OPTION_COST_CENTERS,
            static::OPTION_BUDGETS,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function expandOptions(array $options, Request $request): array
    {
        $selectedBudgetIds = array_values(
            array_filter(
                array_map('intval', (array)$request->query->all('budgetIds')),
            ),
        );

        $options[static::OPTION_COST_CENTERS] = $this->buildCostCenterChoices();
        $options[static::OPTION_BUDGETS] = $this->buildBudgetChoices($selectedBudgetIds);

        return $options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(static::FIELD_COST_CENTER_IDS, ChoiceType::class, [
            'label' => static::LABEL_COST_CENTERS,
            'placeholder' => static::PLACEHOLDER_COST_CENTERS,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'choices' => $options[static::OPTION_COST_CENTERS] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => static::PLACEHOLDER_COST_CENTERS,
                'data-clearable' => true,
                'data-dependent-name' => static::PARAM_COST_CENTER_IDS,
            ],
        ]);

        $builder->add(static::FIELD_BUDGET_IDS, Select2ComboBoxType::class, [
            'label' => static::LABEL_BUDGETS,
            'placeholder' => static::PLACEHOLDER_BUDGETS,
            'required' => false,
            'multiple' => true,
            'choices' => $options[static::OPTION_BUDGETS] ?? [],
            'attr' => [
                'data-placeholder' => static::PLACEHOLDER_BUDGETS,
                'data-autocomplete-url' => static::ROUTE_BUDGET_SUGGEST,
                'data-depends-on-field' => sprintf('#%s', static::FIELD_COST_CENTER_IDS),
                'dependent-autocomplete-key' => static::PARAM_COST_CENTER_IDS,
                'data-minimum-input-length' => 0,
                'data-dependent-reset-on-change' => true,
                'data-clearable' => true,
            ],
        ]);
    }

    /**
     * @return array<string, int>
     */
    protected function buildCostCenterChoices(): array
    {
        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setPagination(
                (new PaginationTransfer())->setLimit($this->purchasingControlConfig->getCostCenterFilterLimit()),
            );

        $costCenterChoices = [];

        foreach ($this->purchasingControlFacade->getCostCenterCollection($costCenterCriteriaTransfer)->getCostCenters() as $costCenterTransfer) {
            $costCenterChoices[$costCenterTransfer->getNameOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        return $costCenterChoices;
    }

    /**
     * @param array<int> $selectedBudgetIds
     *
     * @return array<string, int>
     */
    protected function buildBudgetChoices(array $selectedBudgetIds): array
    {
        if (!$selectedBudgetIds) {
            return [];
        }

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions(
                (new BudgetConditionsTransfer())->setBudgetIds($selectedBudgetIds),
            );

        $budgetChoices = [];
        foreach ($this->purchasingControlFacade->getBudgetCollection($budgetCriteriaTransfer)->getBudgets() as $budgetTransfer) {
            $budgetChoices[$budgetTransfer->getNameOrFail()] = $budgetTransfer->getIdBudgetOrFail();
        }

        return $budgetChoices;
    }
}

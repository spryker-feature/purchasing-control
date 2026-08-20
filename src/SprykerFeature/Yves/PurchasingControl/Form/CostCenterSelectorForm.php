<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use Spryker\Yves\Kernel\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class CostCenterSelectorForm extends AbstractType
{
    public const string FIELD_ID_COST_CENTER = 'idCostCenter';

    public const string FIELD_ID_BUDGET = 'idBudget';

    public const string FIELD_APPLY = 'apply';

    public const string OPTION_COST_CENTER_CHOICES = 'cost_center_choices';

    public const string OPTION_BUDGET_CHOICES = 'budget_choices';

    public const string OPTION_BUDGET_LABELS = 'budget_labels';

    public const string OPTION_BUDGET_CHOICE_ATTRS = 'budget_choice_attrs';

    public const string OPTION_BUDGET_COST_CENTER_MAP = 'budget_cost_center_map';

    protected const string GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH = 'purchasing_control.budget.validation.cost_center_mismatch';

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addIdCostCenterField($builder, $options)
            ->addIdBudgetField($builder, $options)
            ->addApplyField($builder)
            ->addBudgetCostCenterConsistencyValidation($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            static::OPTION_COST_CENTER_CHOICES,
            static::OPTION_BUDGET_CHOICES,
            static::OPTION_BUDGET_LABELS,
            static::OPTION_BUDGET_CHOICE_ATTRS,
            static::OPTION_BUDGET_COST_CENTER_MAP,
        ]);
        $resolver->setAllowedTypes(static::OPTION_COST_CENTER_CHOICES, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_CHOICES, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_LABELS, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_CHOICE_ATTRS, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_COST_CENTER_MAP, 'array');
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addIdCostCenterField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_ID_COST_CENTER, ChoiceType::class, [
            'choices' => $options[static::OPTION_COST_CENTER_CHOICES],
            'label' => 'purchasing_control.selector.label',
            'required' => true,
            'placeholder' => 'purchasing_control.selector.placeholder',
            'constraints' => [
                new NotBlank(),
                new Choice(['choices' => array_values($options[static::OPTION_COST_CENTER_CHOICES])]),
            ],
            'attr' => [
                'id' => 'cost-center-select',
                'class' => 'cost-center-selector__select',
            ],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addBudgetCostCenterConsistencyValidation(FormBuilderInterface $builder, array $options): static
    {
        // The budget field offers the budgets of every cost center, so a mismatching pair reaches
        // the server whenever the browser side filtering is bypassed.
        $budgetCostCenterMap = $options[static::OPTION_BUDGET_COST_CENTER_MAP];

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $formEvent) use ($budgetCostCenterMap): void {
            $formData = $formEvent->getData();
            $idBudget = $formData[static::FIELD_ID_BUDGET] ?? null;

            if ($idBudget === null) {
                return;
            }

            if (($budgetCostCenterMap[$idBudget] ?? null) === ($formData[static::FIELD_ID_COST_CENTER] ?? null)) {
                return;
            }

            $formEvent->getForm()
                ->get(static::FIELD_ID_BUDGET)
                ->addError(new FormError(static::GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH));
        });

        return $this;
    }

    protected function addApplyField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_APPLY, SubmitType::class, [
            'label' => 'purchasing_control.budget.apply',
            'attr' => ['class' => 'button button--primary'],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addIdBudgetField(FormBuilderInterface $builder, array $options): static
    {
        $budgetIds = $options[static::OPTION_BUDGET_CHOICES];
        $budgetLabels = $options[static::OPTION_BUDGET_LABELS];
        $budgetChoiceAttrs = $options[static::OPTION_BUDGET_CHOICE_ATTRS];
        $hasBudgets = (bool)$budgetIds;

        $builder->add(static::FIELD_ID_BUDGET, ChoiceType::class, [
            'choices' => $budgetIds,
            'choice_label' => static function (int $budgetId) use ($budgetLabels): string {
                return $budgetLabels[$budgetId];
            },
            'label' => 'purchasing_control.budget.selector.label',
            'required' => $hasBudgets,
            'placeholder' => $hasBudgets ? 'purchasing_control.budget.selector.placeholder' : false,
            'constraints' => $hasBudgets ? [new Choice(['choices' => $budgetIds])] : [],
            'choice_attr' => static function (int $budgetId) use ($budgetChoiceAttrs): array {
                return $budgetChoiceAttrs[$budgetId] ?? [];
            },
            'attr' => [
                'id' => 'budget-select',
                'class' => 'cost-center-selector__select',
            ],
        ]);

        return $this;
    }
}

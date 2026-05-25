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

    public const string OPTION_BUDGET_CHOICE_ATTRS = 'budget_choice_attrs';

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addIdCostCenterField($builder, $options)
            ->addIdBudgetField($builder, $options)
            ->addApplyField($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([static::OPTION_COST_CENTER_CHOICES, static::OPTION_BUDGET_CHOICES, static::OPTION_BUDGET_CHOICE_ATTRS]);
        $resolver->setAllowedTypes(static::OPTION_COST_CENTER_CHOICES, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_CHOICES, 'array');
        $resolver->setAllowedTypes(static::OPTION_BUDGET_CHOICE_ATTRS, 'array');
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
                'onchange' => 'this.form.submit()',
            ],
        ]);

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
        $budgetChoices = $options[static::OPTION_BUDGET_CHOICES];
        $budgetChoiceAttrs = $options[static::OPTION_BUDGET_CHOICE_ATTRS];
        $hasBudgets = (bool)$budgetChoices;

        $builder->add(static::FIELD_ID_BUDGET, ChoiceType::class, [
            'choices' => $budgetChoices,
            'label' => 'purchasing_control.budget.selector.label',
            'required' => $hasBudgets,
            'placeholder' => $hasBudgets ? 'purchasing_control.budget.selector.placeholder' : false,
            'constraints' => $hasBudgets ? [new Choice(['choices' => array_values($budgetChoices)])] : [],
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

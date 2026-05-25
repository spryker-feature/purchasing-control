<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use Spryker\Yves\Kernel\Form\AbstractType;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetSearchFilterSubForm extends AbstractType
{
    public const string FIELD_NAME = 'name';

    public const string FIELD_ENFORCEMENT_RULE = 'enforcementRule';

    public const string FIELD_STATUS = 'status';

    public const string FIELD_CURRENCY = 'currency';

    public const string FIELD_STARTS_AT = 'startsAt';

    public const string FIELD_ENDS_AT = 'endsAt';

    public const string STATUS_ACTIVE = '1';

    public const string STATUS_INACTIVE = '0';

    public const string STATUS_ARCHIVED = 'archived';

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            BudgetSearchForm::OPTION_CURRENCY_CHOICES,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addNameField($builder)
            ->addEnforcementRuleField($builder)
            ->addStatusField($builder)
            ->addCurrencyField($builder, $options)
            ->addStartsAtField($builder)
            ->addEndsAtField($builder);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => 'purchasing_control.budget.form.name',
            'required' => false,
            'sanitize_xss' => true,
            'attr' => [
                'placeholder' => 'purchasing_control.budget.search.name_placeholder',
            ],
        ]);

        return $this;
    }

    protected function addEnforcementRuleField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ENFORCEMENT_RULE, ChoiceType::class, [
            'label' => 'purchasing_control.budget.list.column.enforcement_rule',
            'choices' => [
                'purchasing_control.budget.form.enforcement_rule.block' => PurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
                'purchasing_control.budget.form.enforcement_rule.warn' => PurchasingControlConfig::ENFORCEMENT_RULE_WARN,
                'purchasing_control.budget.form.enforcement_rule.require_approval' => PurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL,
            ],
            'required' => false,
            'placeholder' => 'purchasing_control.budget.search.all_enforcement_rules',
        ]);

        return $this;
    }

    protected function addStatusField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_STATUS, ChoiceType::class, [
            'choices' => [
                'purchasing_control.budget.search.status.all' => '',
                'purchasing_control.budget.search.status.active' => static::STATUS_ACTIVE,
                'purchasing_control.budget.search.status.inactive' => static::STATUS_INACTIVE,
                'purchasing_control.budget.search.status.archived' => static::STATUS_ARCHIVED,
            ],
            'required' => false,
            'placeholder' => false,
            'label' => 'purchasing_control.budget.list.column.status',
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addCurrencyField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_CURRENCY, ChoiceType::class, [
            'choices' => $options[BudgetSearchForm::OPTION_CURRENCY_CHOICES],
            'required' => false,
            'placeholder' => 'purchasing_control.budget.search.all_currencies',
            'label' => 'purchasing_control.budget.list.column.currency',
        ]);

        return $this;
    }

    protected function addStartsAtField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_STARTS_AT, DateType::class, [
            'label' => 'purchasing_control.budget.search.starts_at',
            'widget' => 'single_text',
            'input' => 'string',
            'required' => false,
        ]);

        return $this;
    }

    protected function addEndsAtField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ENDS_AT, DateType::class, [
            'label' => 'purchasing_control.budget.search.ends_at',
            'widget' => 'single_text',
            'input' => 'string',
            'required' => false,
        ]);

        return $this;
    }
}

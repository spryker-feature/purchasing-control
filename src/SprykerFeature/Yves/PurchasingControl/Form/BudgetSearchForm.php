<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use Spryker\Yves\Kernel\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetSearchForm extends AbstractType
{
    public const string FORM_NAME = 'budgetSearchForm';

    public const string FIELD_RESET = 'reset';

    public const string FIELD_FILTERS = 'filters';

    public const string FIELD_COST_CENTER_UUID = 'costCenterUuid';

    public const string FIELD_ORDER_BY = 'orderBy';

    public const string FIELD_ORDER_DIRECTION = 'orderDirection';

    public const string OPTION_CURRENCY_CHOICES = 'OPTION_CURRENCY_CHOICES';

    public const string OPTION_COST_CENTER_UUID = 'OPTION_COST_CENTER_UUID';

    protected const string ORDER_DIRECTION_ASC = 'ASC';

    public function getBlockPrefix(): string
    {
        return static::FORM_NAME;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            static::OPTION_CURRENCY_CHOICES => [],
            static::OPTION_COST_CENTER_UUID => '',
            'csrf_protection' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $this->addResetField($builder)
            ->addOrderByField($builder)
            ->addOrderDirectionField($builder)
            ->addCostCenterUuidField($builder, $options)
            ->addFiltersField($builder, $options);
    }

    protected function addResetField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_RESET, HiddenType::class, [
            'required' => false,
            'label' => false,
        ]);

        return $this;
    }

    protected function addOrderByField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ORDER_BY, HiddenType::class, [
            'required' => false,
            'label' => false,
        ]);

        return $this;
    }

    protected function addOrderDirectionField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ORDER_DIRECTION, HiddenType::class, [
            'required' => false,
            'label' => false,
            'data' => static::ORDER_DIRECTION_ASC,
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addCostCenterUuidField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_COST_CENTER_UUID, HiddenType::class, [
            'required' => false,
            'label' => false,
            'empty_data' => (string)$options[static::OPTION_COST_CENTER_UUID],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addFiltersField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(
            static::FIELD_FILTERS,
            BudgetSearchFilterSubForm::class,
            [
                static::OPTION_CURRENCY_CHOICES => $options[static::OPTION_CURRENCY_CHOICES],
            ],
        );

        return $this;
    }
}

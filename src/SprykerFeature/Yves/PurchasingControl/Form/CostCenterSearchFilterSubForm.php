<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use Spryker\Yves\Kernel\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class CostCenterSearchFilterSubForm extends AbstractType
{
    public const string FIELD_NAME = 'name';

    public const string FIELD_ID_COMPANY_BUSINESS_UNIT = 'idCompanyBusinessUnit';

    public const string FIELD_STATUS = 'status';

    public const string STATUS_ACTIVE = '1';

    public const string STATUS_INACTIVE = '0';

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            CostCenterSearchForm::OPTION_BUSINESS_UNIT_CHOICES,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addNameField($builder)
            ->addBusinessUnitField($builder, $options)
            ->addStatusField($builder);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => 'purchasing_control.cost_center.form.name',
            'required' => false,
            'sanitize_xss' => true,
            'attr' => [
                'placeholder' => 'purchasing_control.cost_center.search.name_placeholder',
            ],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addBusinessUnitField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_ID_COMPANY_BUSINESS_UNIT, ChoiceType::class, [
            'choices' => $options[CostCenterSearchForm::OPTION_BUSINESS_UNIT_CHOICES],
            'required' => false,
            'placeholder' => 'purchasing_control.cost_center.search.all_business_units',
            'label' => 'purchasing_control.cost_center.form.business_unit',
        ]);

        return $this;
    }

    protected function addStatusField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_STATUS, ChoiceType::class, [
            'choices' => [
                'purchasing_control.cost_center.search.status.all' => '',
                'purchasing_control.cost_center.search.status.active' => static::STATUS_ACTIVE,
                'purchasing_control.cost_center.search.status.inactive' => static::STATUS_INACTIVE,
            ],
            'required' => false,
            'placeholder' => false,
            'label' => 'purchasing_control.cost_center.list.status',
        ]);

        return $this;
    }
}

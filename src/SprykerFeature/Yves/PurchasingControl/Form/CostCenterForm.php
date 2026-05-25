<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Yves\Kernel\Form\AbstractType;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CostCenterForm extends AbstractType
{
    public const string FORM_NAME = 'costCenterForm';

    public const string FIELD_NAME = 'name';

    public const string FIELD_DESCRIPTION = 'description';

    public const string FIELD_COMPANY_BUSINESS_UNIT_IDS = 'companyBusinessUnitIds';

    public const string FIELD_IS_ACTIVE = 'isActive';

    public const string OPTION_BUSINESS_UNIT_CHOICES = 'businessUnitChoices';

    public const string OPTION_SELECTED_BUSINESS_UNIT_IDS = 'selectedBusinessUnitIds';

    public function getBlockPrefix(): string
    {
        return static::FORM_NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addNameField($builder)
            ->addDescriptionField($builder)
            ->addBusinessUnitsField($builder, $options)
            ->addIsActiveField($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CostCenterTransfer::class,
            static::OPTION_BUSINESS_UNIT_CHOICES => [],
            static::OPTION_SELECTED_BUSINESS_UNIT_IDS => [],
        ]);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => 'purchasing_control.cost_center.form.name',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => PurchasingControlConfig::NAME_MAX_LENGTH, 'maxMessage' => 'purchasing_control.cost_center.validation.name_too_long']),
            ],
            'attr' => ['data-qa' => 'cost-center-name-input', 'maxlength' => PurchasingControlConfig::NAME_MAX_LENGTH],
        ]);

        return $this;
    }

    protected function addDescriptionField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_DESCRIPTION, TextareaType::class, [
            'label' => 'purchasing_control.cost_center.form.description',
            'required' => false,
            'attr' => ['data-qa' => 'cost-center-description-input'],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addBusinessUnitsField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_COMPANY_BUSINESS_UNIT_IDS, ChoiceType::class, [
            'label' => 'purchasing_control.cost_center.form.business_unit',
            'choices' => $options[static::OPTION_BUSINESS_UNIT_CHOICES],
            'data' => $options[static::OPTION_SELECTED_BUSINESS_UNIT_IDS],
            'mapped' => false,
            'required' => true,
            'multiple' => true,
            'expanded' => true,
            'constraints' => [new NotBlank()],
        ]);

        return $this;
    }

    protected function addIsActiveField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_IS_ACTIVE, CheckboxType::class, [
            'label' => 'purchasing_control.cost_center.form.is_active',
            'required' => false,
        ]);

        return $this;
    }
}

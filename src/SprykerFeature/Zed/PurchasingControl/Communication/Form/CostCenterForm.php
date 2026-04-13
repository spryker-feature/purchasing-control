<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form;

use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterForm extends AbstractType
{
    public const FIELD_NAME = 'name';

    public const FIELD_DESCRIPTION = 'description';

    public const FIELD_COMPANY_BUSINESS_UNIT_IDS = 'companyBusinessUnitIds';

    public const FIELD_IS_ACTIVE = 'isActive';

    public const OPTION_BUSINESS_UNIT_CHOICES = 'businessUnitChoices';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addNameField($builder)
            ->addDescriptionField($builder)
            ->addCompanyBusinessUnitIdsField($builder, $options)
            ->addIsActiveField($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            static::OPTION_BUSINESS_UNIT_CHOICES => [],
        ]);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => 'Name',
            'constraints' => [new NotBlank()],
            'attr' => ['data-qa' => 'cost-center-name'],
        ]);

        return $this;
    }

    protected function addDescriptionField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_DESCRIPTION, TextareaType::class, [
            'label' => 'Description',
            'required' => false,
            'attr' => ['data-qa' => 'cost-center-description'],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addCompanyBusinessUnitIdsField(FormBuilderInterface $builder, array $options): static
    {
        // Projects with large BU datasets should override `getBusinessUnitSelectLimit()` or switch to async autocomplete
        $builder->add(static::FIELD_COMPANY_BUSINESS_UNIT_IDS, ChoiceType::class, [
            'label' => 'Business Units',
            'choices' => $options[static::OPTION_BUSINESS_UNIT_CHOICES],
            'multiple' => true,
            'expanded' => false,
            'placeholder' => false,
            'required' => true,
            'constraints' => [new Count(['min' => 1, 'minMessage' => 'At least one business unit must be selected.'])],
            'attr' => [
                'data-qa' => 'cost-center-business-unit-ids',
                'class' => 'spryker-form-select2combobox',
            ],
        ]);

        return $this;
    }

    protected function addIsActiveField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_IS_ACTIVE, CheckboxType::class, [
            'label' => 'Active',
            'required' => false,
            'attr' => ['data-qa' => 'cost-center-is-active'],
        ]);

        return $this;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form;

use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterForm extends AbstractType
{
    public const string FORM_NAME = 'costCenterForm';

    public const string FIELD_NAME = 'name';

    public const string FIELD_DESCRIPTION = 'description';

    public const string FIELD_COMPANY = 'idCompany';

    public const string FIELD_COMPANY_BUSINESS_UNIT_IDS = 'companyBusinessUnitIds';

    public const string FIELD_IS_ACTIVE = 'isActive';

    public const string OPTION_COMPANY_CHOICES = 'companyChoices';

    public const string OPTION_BUSINESS_UNIT_CHOICES = 'businessUnitChoices';

    /**
     * @uses \Spryker\Zed\CompanyGui\Communication\Controller\SuggestController::indexAction()
     */
    protected const string ROUTE_COMPANY_SUGGEST = '/company-gui/suggest';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnitGui\Communication\Controller\SuggestController::indexAction()
     */
    protected const string ROUTE_BUSINESS_UNIT_SUGGEST = '/company-business-unit-gui/suggest?';

    protected const string PLACEHOLDER_SEARCH = 'Start typing to search...';

    protected const string CSS_CLASS_COMPANY_DEPENDABLE = 'js-select-dependable--company';

    protected const string LABEL_NAME = 'Name';

    protected const string LABEL_DESCRIPTION = 'Description';

    protected const string LABEL_COMPANY = 'Company';

    protected const string LABEL_BUSINESS_UNITS = 'Business Units';

    protected const string LABEL_IS_ACTIVE = 'Active';

    public function getBlockPrefix(): string
    {
        return static::FORM_NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addNameField($builder)
            ->addDescriptionField($builder)
            ->addCompanyField($builder, $options)
            ->addCompanyBusinessUnitIdsField($builder, $options)
            ->addIsActiveField($builder);

        $this->addAssignedBusinessUnitDataListener($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CostCenterTransfer::class,
            static::OPTION_COMPANY_CHOICES => [],
            static::OPTION_BUSINESS_UNIT_CHOICES => [],
        ]);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => static::LABEL_NAME,
            'constraints' => [
                new NotBlank(),
                new Length(['max' => PurchasingControlConfig::NAME_MAX_LENGTH, 'maxMessage' => 'purchasing_control.cost_center.validation.name_too_long']),
            ],
            'attr' => ['data-qa' => 'cost-center-name', 'maxlength' => PurchasingControlConfig::NAME_MAX_LENGTH],
        ]);

        return $this;
    }

    protected function addDescriptionField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_DESCRIPTION, TextareaType::class, [
            'label' => static::LABEL_DESCRIPTION,
            'required' => false,
            'attr' => ['data-qa' => 'cost-center-description'],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addCompanyField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_COMPANY, Select2ComboBoxType::class, [
            'label' => static::LABEL_COMPANY,
            'choices' => $options[static::OPTION_COMPANY_CHOICES],
            'multiple' => false,
            'mapped' => false,
            'required' => true,
            'attr' => [
                'data-qa' => 'cost-center-company',
                'data-autocomplete-url' => static::ROUTE_COMPANY_SUGGEST,
                'data-minimum-input-length' => 0,
                'data-dependent-name' => 'idsCompany',
                'placeholder' => static::PLACEHOLDER_SEARCH,
                'data-clearable' => true,
                'class' => static::CSS_CLASS_COMPANY_DEPENDABLE . ' spryker-form-select2combobox',
            ],
        ]);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function addCompanyBusinessUnitIdsField(FormBuilderInterface $builder, array $options): static
    {
        $builder->add(static::FIELD_COMPANY_BUSINESS_UNIT_IDS, Select2ComboBoxType::class, [
            'label' => static::LABEL_BUSINESS_UNITS,
            'choices' => $options[static::OPTION_BUSINESS_UNIT_CHOICES],
            'multiple' => true,
            'required' => true,
            'constraints' => [new Count(['min' => 1, 'minMessage' => 'At least one business unit must be selected.'])],
            'attr' => [
                'data-qa' => 'cost-center-business-unit-ids',
                'data-autocomplete-url' => static::ROUTE_BUSINESS_UNIT_SUGGEST,
                'data-clear-initial' => false,
                'dependent-autocomplete-key' => 'idsCompany',
                'data-minimum-input-length' => 0,
                'data-depends-on-field' => '#costCenterForm_idCompany',
                'data-dependent-reset-on-change' => true,
                'placeholder' => static::PLACEHOLDER_SEARCH,
                'class' => 'js-select-dependable js-select-dependable--business-unit spryker-form-select2combobox',
            ],
        ]);

        return $this;
    }

    protected function addIsActiveField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_IS_ACTIVE, CheckboxType::class, [
            'label' => static::LABEL_IS_ACTIVE,
            'required' => false,
            'attr' => ['data-qa' => 'cost-center-is-active'],
        ]);

        return $this;
    }

    protected function addAssignedBusinessUnitDataListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event): void {
                $costCenterTransfer = $event->getData();

                if (!$costCenterTransfer instanceof CostCenterTransfer) {
                    return;
                }

                $form = $event->getForm();
                if (!$form->has(static::FIELD_COMPANY)) {
                    return;
                }

                $companyChoices = $form->getConfig()->getOption(static::OPTION_COMPANY_CHOICES);
                $companyId = (bool)$companyChoices ? reset($companyChoices) : null;

                $form->get(static::FIELD_COMPANY)->setData($companyId);
            },
        );
    }
}

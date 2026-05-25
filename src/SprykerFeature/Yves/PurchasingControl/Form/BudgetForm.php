<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form;

use DateTime;
use Generated\Shared\Transfer\BudgetTransfer;
use Spryker\Yves\Kernel\Form\AbstractType;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BudgetForm extends AbstractType
{
    public const string FORM_NAME = 'budgetForm';

    public const string FIELD_NAME = 'name';

    public const string FIELD_AMOUNT = 'amount';

    public const string FIELD_CURRENCY_ISO_CODE = 'currencyIsoCode';

    public const string FIELD_STARTS_AT = 'startsAt';

    public const string FIELD_ENDS_AT = 'endsAt';

    public const string FIELD_ENFORCEMENT_RULE = 'enforcementRule';

    public const string FIELD_IS_ACTIVE = 'isActive';

    public const string OPTION_CURRENCY_CHOICES = 'currency_choices';

    public function getBlockPrefix(): string
    {
        return static::FORM_NAME;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addNameField($builder)
            ->addAmountField($builder)
            ->addCurrencyIsoCodeField($builder, $options[static::OPTION_CURRENCY_CHOICES])
            ->addEnforcementRuleField($builder)
            ->addStartsAtField($builder)
            ->addEndsAtField($builder)
            ->addIsActiveField($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BudgetTransfer::class,
            static::OPTION_CURRENCY_CHOICES => [],
            'constraints' => [
                new Callback(function (BudgetTransfer $data, ExecutionContextInterface $context): void {
                    if ($data->getStartsAt() && $data->getEndsAt() && $data->getEndsAt() < $data->getStartsAt()) {
                        $context->buildViolation('purchasing_control.budget.validation.date_range_invalid')
                            ->atPath(static::FIELD_ENDS_AT)
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }

    protected function addNameField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_NAME, TextType::class, [
            'label' => 'purchasing_control.budget.form.name',
            'constraints' => [
                new NotBlank(),
                new Length(['max' => PurchasingControlConfig::NAME_MAX_LENGTH, 'maxMessage' => 'purchasing_control.budget.validation.name_too_long']),
            ],
            'attr' => ['data-qa' => 'budget-name-input', 'maxlength' => PurchasingControlConfig::NAME_MAX_LENGTH],
        ]);

        return $this;
    }

    protected function addAmountField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_AMOUNT, NumberType::class, [
            'label' => 'purchasing_control.budget.form.amount',
            'scale' => 2,
            'constraints' => [
                new NotBlank(),
                new GreaterThan(['value' => 0]),
                new LessThanOrEqual(['value' => PurchasingControlConfig::BUDGET_AMOUNT_MAX, 'message' => 'purchasing_control.budget.validation.amount_too_large']),
            ],
            'attr' => ['data-qa' => 'budget-amount-input'],
        ]);

        $builder->get(static::FIELD_AMOUNT)->addModelTransformer(
            new CallbackTransformer(
                fn (?int $cents) => $cents !== null ? $cents / 100 : null,
                fn (?float $amount) => $amount !== null ? (int)round($amount * 100) : null,
            ),
        );

        return $this;
    }

    /**
     * @param array<string, string> $currencyChoices
     */
    protected function addCurrencyIsoCodeField(FormBuilderInterface $builder, array $currencyChoices): static
    {
        $builder->add(static::FIELD_CURRENCY_ISO_CODE, ChoiceType::class, [
            'label' => 'purchasing_control.budget.form.currency',
            'choices' => $currencyChoices,
            'constraints' => [new NotBlank()],
            'attr' => ['data-qa' => 'budget-currency-select'],
        ]);

        return $this;
    }

    protected function addStartsAtField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_STARTS_AT, DateType::class, [
            'label' => 'purchasing_control.budget.form.starts_at',
            'widget' => 'single_text',
            'input' => 'string',
            'constraints' => [new NotBlank()],
            'attr' => ['data-qa' => 'budget-starts-at-input'],
        ]);

        return $this;
    }

    protected function addEndsAtField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ENDS_AT, DateType::class, [
            'label' => 'purchasing_control.budget.form.ends_at',
            'widget' => 'single_text',
            'input' => 'string',
            'constraints' => [
                new NotBlank(),
                new GreaterThanOrEqual([
                    'value' => (new DateTime('today'))->format('Y-m-d'),
                    'message' => 'purchasing_control.budget.validation.end_date_in_past',
                ]),
            ],
            'attr' => ['data-qa' => 'budget-ends-at-input', 'min' => (new DateTime('today'))->format('Y-m-d')],
        ]);

        return $this;
    }

    protected function addEnforcementRuleField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_ENFORCEMENT_RULE, ChoiceType::class, [
            'label' => 'purchasing_control.budget.form.enforcement_rule',
            'choices' => [
                'purchasing_control.budget.form.enforcement_rule.block' => PurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
                'purchasing_control.budget.form.enforcement_rule.warn' => PurchasingControlConfig::ENFORCEMENT_RULE_WARN,
                'purchasing_control.budget.form.enforcement_rule.require_approval' => PurchasingControlConfig::ENFORCEMENT_RULE_REQUIRE_APPROVAL,
            ],
            'constraints' => [new NotBlank()],
            'attr' => ['data-qa' => 'budget-enforcement-rule-select'],
        ]);

        return $this;
    }

    protected function addIsActiveField(FormBuilderInterface $builder): static
    {
        $builder->add(static::FIELD_IS_ACTIVE, CheckboxType::class, [
            'label' => 'purchasing_control.budget.form.is_active',
            'required' => false,
        ]);

        return $this;
    }
}

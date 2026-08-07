<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RecurringScheduleTransfer;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use SprykerFeature\Yves\PurchasingControl\Formatter\BudgetSummaryFormatterInterface;
use SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CostCenterBudgetFormFieldExpander implements CostCenterBudgetFormFieldExpanderInterface
{
    protected const string FIELD_ID_COST_CENTER = 'idCostCenter';

    protected const string FIELD_ID_BUDGET = 'idBudget';

    protected const string LABEL_COST_CENTER = 'purchasing_control.selector.label';

    protected const string LABEL_BUDGET = 'purchasing_control.budget.selector.label';

    protected const string PLACEHOLDER_COST_CENTER = 'purchasing_control.selector.placeholder';

    protected const string PLACEHOLDER_BUDGET = 'purchasing_control.budget.selector.placeholder';

    protected const string GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH = 'purchasing_control.recurring_order.budget_cost_center_mismatch';

    protected const string GLOSSARY_KEY_COST_CENTER_REQUIRED = 'purchasing_control.recurring_order.cost_center_required';

    protected const string GLOSSARY_KEY_BUDGET_REQUIRED = 'purchasing_control.recurring_order.budget_required';

    protected const string GLOSSARY_KEY_INACTIVE_COST_CENTER = 'purchasing_control.validation.inactive-cost-center';

    protected const string GLOSSARY_KEY_INACTIVE_BUDGET = 'purchasing_control.validation.inactive-budget';

    protected const string DATA_QA_COST_CENTER_SELECT = 'recurring-order-review-cost-center-select';

    protected const string DATA_QA_BUDGET_SELECT = 'recurring-order-review-budget-select';

    public function __construct(
        protected readonly CustomerClientInterface $customerClient,
        protected readonly CostCenterReaderInterface $costCenterReader,
        protected readonly BudgetSummaryFormatterInterface $budgetSummaryFormatter,
        protected readonly UtilEncodingServiceInterface $utilEncodingService,
        protected readonly CurrencyClientInterface $currencyClient,
        protected readonly PurchasingControlConfig $purchasingControlConfig,
    ) {
    }

    public function expandForm(
        FormBuilderInterface $builder,
        ?RecurringScheduleTransfer $recurringScheduleTransfer,
        bool $isSelectionRequired = false,
    ): void {
        $idCompanyBusinessUnit = $this->customerClient->getCustomer()?->getCompanyUserTransfer()?->getFkCompanyBusinessUnit();
        $currencyIsoCode = $recurringScheduleTransfer?->getCurrencyIsoCode()
            ?? $this->currencyClient->getCurrent()->getCode();

        if ($idCompanyBusinessUnit === null || $currencyIsoCode === null) {
            return;
        }

        $costCenterCollectionTransfer = $this->costCenterReader->getActiveCostCentersForCompanyBusinessUnit(
            $idCompanyBusinessUnit,
            $currencyIsoCode,
        );

        $this->addUnavailableSelectionListener($builder, $costCenterCollectionTransfer, $currencyIsoCode, $isSelectionRequired);

        $isCostCenterFieldAdded = $this->addCostCenterField($builder, $costCenterCollectionTransfer, $isSelectionRequired);
        $isBudgetFieldAdded = $this->addBudgetField($builder, $costCenterCollectionTransfer, $currencyIsoCode, $isSelectionRequired);

        $this->addCurrentSelectionPreset($builder, $recurringScheduleTransfer, $isCostCenterFieldAdded, $isBudgetFieldAdded);
    }

    protected function addCostCenterField(
        FormBuilderInterface $builder,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        bool $isSelectionRequired,
    ): bool {
        $costCenterChoices = $this->buildCostCenterChoices($costCenterCollectionTransfer);

        if ($costCenterChoices === []) {
            return false;
        }

        $builder->add(
            static::FIELD_ID_COST_CENTER,
            ChoiceType::class,
            $this->buildCostCenterFieldOptions($costCenterChoices, $isSelectionRequired),
        );

        return true;
    }

    /**
     * @param array<string, int> $costCenterChoices
     *
     * @return array<string, mixed>
     */
    protected function buildCostCenterFieldOptions(array $costCenterChoices, bool $isSelectionRequired): array
    {
        $constraints = [];

        if ($isSelectionRequired) {
            $constraints[] = new NotBlank(['message' => static::GLOSSARY_KEY_COST_CENTER_REQUIRED]);
        }

        return [
            'choices' => $costCenterChoices,
            'required' => $isSelectionRequired,
            'label' => static::LABEL_COST_CENTER,
            'placeholder' => static::PLACEHOLDER_COST_CENTER,
            'invalid_message' => static::GLOSSARY_KEY_INACTIVE_COST_CENTER,
            'attr' => ['data-qa' => static::DATA_QA_COST_CENTER_SELECT],
            'constraints' => $constraints,
        ];
    }

    protected function addBudgetField(
        FormBuilderInterface $builder,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
        bool $isSelectionRequired,
    ): bool {
        $budgetChoices = $this->buildBudgetChoices($costCenterCollectionTransfer);

        if ($budgetChoices === []) {
            return false;
        }

        $builder->add(
            static::FIELD_ID_BUDGET,
            ChoiceType::class,
            $this->buildBudgetFieldOptions($costCenterCollectionTransfer, $budgetChoices, $currencyIsoCode, $isSelectionRequired),
        );

        return true;
    }

    /**
     * @param array<string, int> $budgetChoices
     *
     * @return array<string, mixed>
     */
    protected function buildBudgetFieldOptions(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        array $budgetChoices,
        string $currencyIsoCode,
        bool $isSelectionRequired,
    ): array {
        $budgetCostCenterMap = $this->buildBudgetCostCenterMap($costCenterCollectionTransfer);
        $budgetChoiceAttrs = $this->buildBudgetChoiceAttrs($costCenterCollectionTransfer, $currencyIsoCode);

        $constraints = [
            new Callback(function ($idBudget, ExecutionContextInterface $context) use ($budgetCostCenterMap): void {
                $this->validateBudgetBelongsToCostCenter($idBudget, $context, $budgetCostCenterMap);
            }),
        ];

        if ($isSelectionRequired) {
            array_unshift($constraints, new NotBlank(['message' => static::GLOSSARY_KEY_BUDGET_REQUIRED]));
        }

        return [
            'choices' => $budgetChoices,
            'required' => $isSelectionRequired,
            'label' => static::LABEL_BUDGET,
            'placeholder' => static::PLACEHOLDER_BUDGET,
            'invalid_message' => static::GLOSSARY_KEY_INACTIVE_BUDGET,
            'attr' => ['data-qa' => static::DATA_QA_BUDGET_SELECT],
            'choice_attr' => function ($idBudget) use ($budgetChoiceAttrs): array {
                return $budgetChoiceAttrs[$idBudget] ?? [];
            },
            'constraints' => $constraints,
        ];
    }

    protected function addUnavailableSelectionListener(
        FormBuilderInterface $builder,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
        bool $isSelectionRequired,
    ): void {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) use (
            $costCenterCollectionTransfer,
            $currencyIsoCode,
            $isSelectionRequired,
        ): void {
            $this->restoreUnavailableFields($formEvent, $costCenterCollectionTransfer, $currencyIsoCode, $isSelectionRequired);
        });
    }

    protected function restoreUnavailableFields(
        FormEvent $formEvent,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
        bool $isSelectionRequired,
    ): void {
        $submittedData = $formEvent->getData();

        if (!is_array($submittedData)) {
            return;
        }

        $form = $formEvent->getForm();

        if ($this->isSubmittedFieldMissing($form, $submittedData, static::FIELD_ID_COST_CENTER)) {
            $form->add(
                static::FIELD_ID_COST_CENTER,
                ChoiceType::class,
                $this->buildCostCenterFieldOptions([], $isSelectionRequired),
            );
        }

        if ($this->isSubmittedFieldMissing($form, $submittedData, static::FIELD_ID_BUDGET)) {
            $form->add(
                static::FIELD_ID_BUDGET,
                ChoiceType::class,
                $this->buildBudgetFieldOptions($costCenterCollectionTransfer, [], $currencyIsoCode, $isSelectionRequired),
            );
        }
    }

    /**
     * @param array<string, mixed> $submittedData
     */
    protected function isSubmittedFieldMissing(FormInterface $form, array $submittedData, string $fieldName): bool
    {
        return array_key_exists($fieldName, $submittedData) && !$form->has($fieldName);
    }

    /**
     * @param array<int, int> $budgetCostCenterMap
     */
    protected function validateBudgetBelongsToCostCenter(
        mixed $idBudget,
        ExecutionContextInterface $context,
        array $budgetCostCenterMap,
    ): void {
        if ($idBudget === null || $idBudget === '') {
            return;
        }

        $rootForm = $context->getRoot();
        $idCostCenter = $rootForm->has(static::FIELD_ID_COST_CENTER)
            ? $this->toId($rootForm->get(static::FIELD_ID_COST_CENTER)->getData())
            : null;

        if (($budgetCostCenterMap[(int)$idBudget] ?? null) !== $idCostCenter) {
            $context->addViolation(static::GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH);
        }
    }

    protected function addCurrentSelectionPreset(
        FormBuilderInterface $builder,
        ?RecurringScheduleTransfer $recurringScheduleTransfer,
        bool $isCostCenterFieldAdded,
        bool $isBudgetFieldAdded,
    ): void {
        if ($isCostCenterFieldAdded && $isBudgetFieldAdded && $recurringScheduleTransfer === null) {
            return;
        }

        [$currentIdCostCenter, $currentIdBudget] = $recurringScheduleTransfer !== null
            ? $this->extractCurrentSelection($recurringScheduleTransfer)
            : [null, null];

        $presetIdCostCenter = $isCostCenterFieldAdded ? $currentIdCostCenter : null;
        $presetIdBudget = $isBudgetFieldAdded ? $currentIdBudget : null;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($presetIdCostCenter, $presetIdBudget): void {
            $data = $event->getData() ?? [];

            if (!array_key_exists(static::FIELD_ID_COST_CENTER, $data)) {
                $data[static::FIELD_ID_COST_CENTER] = $presetIdCostCenter;
            }

            if (!array_key_exists(static::FIELD_ID_BUDGET, $data)) {
                $data[static::FIELD_ID_BUDGET] = $presetIdBudget;
            }

            $event->setData($data);
        });
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    protected function extractCurrentSelection(RecurringScheduleTransfer $recurringScheduleTransfer): array
    {
        $quoteData = $recurringScheduleTransfer->getQuoteData();

        if ($quoteData === null) {
            return [null, null];
        }

        $decoded = $this->utilEncodingService->decodeJson($quoteData, true);

        if (!is_array($decoded)) {
            return [null, null];
        }

        return [
            isset($decoded[QuoteTransfer::ID_COST_CENTER]) ? (int)$decoded[QuoteTransfer::ID_COST_CENTER] : null,
            isset($decoded[QuoteTransfer::ID_BUDGET]) ? (int)$decoded[QuoteTransfer::ID_BUDGET] : null,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function buildCostCenterChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $choices[$costCenterTransfer->getNameOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    protected function buildBudgetChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $choices[$budgetTransfer->getNameOrFail()] = $budgetTransfer->getIdBudgetOrFail();
            }
        }

        return $choices;
    }

    /**
     * @return array<int, int>
     */
    protected function buildBudgetCostCenterMap(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $map = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $map[$budgetTransfer->getIdBudgetOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
            }
        }

        return $map;
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function buildBudgetChoiceAttrs(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
    ): array {
        $attrs = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($this->getSelectableBudgets($costCenterTransfer) as $budgetTransfer) {
                $budgetSummaryTransfer = $this->budgetSummaryFormatter->formatBudgetSummary($budgetTransfer, $currencyIsoCode);

                $attrs[$budgetTransfer->getIdBudgetOrFail()] = [
                    'data-cost-center-id' => (string)$costCenterTransfer->getIdCostCenterOrFail(),
                    'data-total-amount' => (string)$budgetSummaryTransfer->getTotalAmount(),
                    'data-used-amount' => (string)$budgetSummaryTransfer->getUsedAmount(),
                    'data-total-formatted' => (string)$budgetSummaryTransfer->getTotalFormatted(),
                    'data-used-formatted' => (string)$budgetSummaryTransfer->getUsedFormatted(),
                    'data-remaining-formatted' => (string)$budgetSummaryTransfer->getRemainingFormatted(),
                    'data-usage-formatted' => (string)$budgetSummaryTransfer->getUsageInfo(),
                ];
            }
        }

        return $attrs;
    }

    /**
     * @return array<\Generated\Shared\Transfer\BudgetTransfer>
     */
    protected function getSelectableBudgets(CostCenterTransfer $costCenterTransfer): array
    {
        $selectableEnforcementRules = $this->purchasingControlConfig->getRecurringOrderSelectableBudgetEnforcementRules();
        $budgetTransfers = [];

        foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
            if (!in_array($budgetTransfer->getEnforcementRule(), $selectableEnforcementRules, true)) {
                continue;
            }

            $budgetTransfers[] = $budgetTransfer;
        }

        return $budgetTransfers;
    }

    protected function toId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int)$value;
    }
}

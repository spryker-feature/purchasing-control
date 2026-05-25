<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\Handler;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetSearchFilterSubForm;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetSearchForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BudgetSearchFormHandler
{
    protected const string ORDER_DIRECTION_DESC = 'DESC';

    protected const string DATE_FORMAT = 'Y-m-d';

    public function buildBudgetCriteriaTransfer(Request $request, FormInterface $form, int $idCostCenter): BudgetCriteriaTransfer
    {
        $budgetConditionsTransfer = (new BudgetConditionsTransfer())
            ->addIdCostCenter($idCostCenter)
            ->setWithBudgetConsumption(true);

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())->setBudgetConditions($budgetConditionsTransfer);

        $formData = $request->query->all()[BudgetSearchForm::FORM_NAME] ?? [];

        if (!empty($formData[BudgetSearchForm::FIELD_RESET])) {
            return $budgetCriteriaTransfer;
        }

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $budgetCriteriaTransfer;
        }

        $filtersForm = $form->get(BudgetSearchForm::FIELD_FILTERS);

        $this->applyNameFilter($filtersForm, $budgetConditionsTransfer);
        $this->applyEnforcementRuleFilter($filtersForm, $budgetConditionsTransfer);
        $this->applyStatusFilter($filtersForm, $budgetConditionsTransfer);
        $this->applyCurrencyFilter($filtersForm, $budgetConditionsTransfer);
        $this->applyPeriodStartFilter($filtersForm, $budgetConditionsTransfer);
        $this->applyPeriodEndFilter($filtersForm, $budgetConditionsTransfer);
        $this->applySort($form, $budgetCriteriaTransfer);

        return $budgetCriteriaTransfer;
    }

    protected function applyNameFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $name = trim((string)$filtersForm->get(BudgetSearchFilterSubForm::FIELD_NAME)->getData());

        if ($name === '') {
            return;
        }

        $budgetConditionsTransfer->addName($name);
    }

    protected function applyEnforcementRuleFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $enforcementRule = $filtersForm->get(BudgetSearchFilterSubForm::FIELD_ENFORCEMENT_RULE)->getData();

        if ($enforcementRule === null || $enforcementRule === '') {
            return;
        }

        $budgetConditionsTransfer->addEnforcementRule((string)$enforcementRule);
    }

    protected function applyStatusFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $status = $filtersForm->get(BudgetSearchFilterSubForm::FIELD_STATUS)->getData();

        if ($status === null || $status === '') {
            return;
        }

        if ($status === BudgetSearchFilterSubForm::STATUS_ARCHIVED) {
            $budgetConditionsTransfer->setEndsAtTo(date(static::DATE_FORMAT, strtotime('-1 day')));

            return;
        }

        $budgetConditionsTransfer->setIsActive((bool)$status);
    }

    protected function applyCurrencyFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $currency = $filtersForm->get(BudgetSearchFilterSubForm::FIELD_CURRENCY)->getData();

        if ($currency === null || $currency === '') {
            return;
        }

        $budgetConditionsTransfer->addCurrencyIsoCode((string)$currency);
    }

    protected function applyPeriodStartFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $startsAt = $filtersForm->get(BudgetSearchFilterSubForm::FIELD_STARTS_AT)->getData();

        if ($startsAt === null || $startsAt === '') {
            return;
        }

        $budgetConditionsTransfer->setStartsAtFrom((string)$startsAt);
    }

    protected function applyPeriodEndFilter(FormInterface $filtersForm, BudgetConditionsTransfer $budgetConditionsTransfer): void
    {
        $endsAt = $filtersForm->get(BudgetSearchFilterSubForm::FIELD_ENDS_AT)->getData();

        if ($endsAt === null || $endsAt === '') {
            return;
        }

        $budgetConditionsTransfer->setEndsAtTo((string)$endsAt);
    }

    protected function applySort(FormInterface $form, BudgetCriteriaTransfer $budgetCriteriaTransfer): void
    {
        $orderBy = (string)$form->get(BudgetSearchForm::FIELD_ORDER_BY)->getData();

        if ($orderBy === '') {
            return;
        }

        $orderDirection = (string)$form->get(BudgetSearchForm::FIELD_ORDER_DIRECTION)->getData();
        $budgetCriteriaTransfer->addSort(
            (new SortTransfer())
                ->setField($orderBy)
                ->setIsAscending(strtoupper($orderDirection) !== static::ORDER_DIRECTION_DESC),
        );
    }
}

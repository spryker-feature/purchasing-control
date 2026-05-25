<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\Handler;

use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\SortTransfer;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSearchFilterSubForm;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSearchForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CostCenterSearchFormHandler
{
    protected const string ORDER_DIRECTION_DESC = 'DESC';

    public function buildCostCenterCriteriaTransfer(Request $request, FormInterface $form, int $idCompany): CostCenterCriteriaTransfer
    {
        $costCenterConditionsTransfer = (new CostCenterConditionsTransfer())->addIdCompany($idCompany);
        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())->setCostCenterConditions($costCenterConditionsTransfer);

        $formData = $request->query->all()[CostCenterSearchForm::FORM_NAME] ?? [];

        if (!empty($formData[CostCenterSearchForm::FIELD_RESET])) {
            return $costCenterCriteriaTransfer;
        }

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $costCenterCriteriaTransfer;
        }

        $filtersForm = $form->get(CostCenterSearchForm::FIELD_FILTERS);

        $this->applyNameFilter($filtersForm, $costCenterConditionsTransfer);
        $this->applyBusinessUnitFilter($filtersForm, $costCenterConditionsTransfer);
        $this->applyStatusFilter($filtersForm, $costCenterConditionsTransfer);
        $this->applySort($form, $costCenterCriteriaTransfer);

        return $costCenterCriteriaTransfer;
    }

    protected function applyNameFilter(FormInterface $filtersForm, CostCenterConditionsTransfer $costCenterConditionsTransfer): void
    {
        $name = trim((string)$filtersForm->get(CostCenterSearchFilterSubForm::FIELD_NAME)->getData());

        if ($name === '') {
            return;
        }

        $costCenterConditionsTransfer->setName($name);
    }

    protected function applyBusinessUnitFilter(FormInterface $filtersForm, CostCenterConditionsTransfer $costCenterConditionsTransfer): void
    {
        $idCompanyBusinessUnit = $filtersForm->get(CostCenterSearchFilterSubForm::FIELD_ID_COMPANY_BUSINESS_UNIT)->getData();

        if ($idCompanyBusinessUnit === null || $idCompanyBusinessUnit === '') {
            return;
        }

        $costCenterConditionsTransfer->addIdCompanyBusinessUnit((int)$idCompanyBusinessUnit);
    }

    protected function applyStatusFilter(FormInterface $filtersForm, CostCenterConditionsTransfer $costCenterConditionsTransfer): void
    {
        $status = $filtersForm->get(CostCenterSearchFilterSubForm::FIELD_STATUS)->getData();

        if ($status === null || $status === '') {
            return;
        }

        $costCenterConditionsTransfer->setIsActive((bool)$status);
    }

    protected function applySort(FormInterface $form, CostCenterCriteriaTransfer $costCenterCriteriaTransfer): void
    {
        $orderBy = (string)$form->get(CostCenterSearchForm::FIELD_ORDER_BY)->getData();

        if ($orderBy === '') {
            return;
        }

        $orderDirection = (string)$form->get(CostCenterSearchForm::FIELD_ORDER_DIRECTION)->getData();
        $costCenterCriteriaTransfer->addSort(
            (new SortTransfer())
                ->setField($orderBy)
                ->setIsAscending(strtoupper($orderDirection) !== static::ORDER_DIRECTION_DESC),
        );
    }
}

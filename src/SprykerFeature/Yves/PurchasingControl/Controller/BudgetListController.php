<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Yves\Kernel\View\View;
use SprykerFeature\Yves\PurchasingControl\Form\BudgetSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetListController extends AbstractPurchasingControlController
{
    protected const string REQUEST_PARAM_COST_CENTER_UUID = 'costCenterUuid';

    protected const string REQUEST_PARAM_PAGE = 'page';

    public function indexAction(Request $request): View
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();

        $formData = $request->query->all(BudgetSearchForm::FORM_NAME);
        $costCenterUuid = (string)($request->query->get(static::REQUEST_PARAM_COST_CENTER_UUID)
            ?: ($formData[BudgetSearchForm::FIELD_COST_CENTER_UUID] ?? ''));

        $costCenterTransfer = $this->requireCostCenterForCompany($costCenterUuid, $companyUserTransfer->getFkCompanyOrFail());

        $form = $this->getFactory()->createBudgetSearchForm($costCenterUuid);

        $budgetCriteriaTransfer = $this->getFactory()
            ->createBudgetSearchFormHandler()
            ->buildBudgetCriteriaTransfer($request, $form, $costCenterTransfer->getIdCostCenterOrFail());

        $budgetCriteriaTransfer->setPagination($this->buildPaginationTransfer($request));

        $budgetCollectionTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->getBudgetCollection($budgetCriteriaTransfer);

        return $this->view(
            [
                'costCenter' => $costCenterTransfer,
                'budgets' => $budgetCollectionTransfer->getBudgets(),
                'pagination' => $budgetCollectionTransfer->getPagination(),
                'searchForm' => $form->createView(),
            ],
            [],
            '@PurchasingControl/views/list-budget/list-budget.twig',
        );
    }

    protected function buildPaginationTransfer(Request $request): PaginationTransfer
    {
        return (new PaginationTransfer())
            ->setMaxPerPage($this->getFactory()->getConfig()->getBudgetListDefaultItemsPerPage())
            ->setPage((int)$request->query->get(static::REQUEST_PARAM_PAGE, 1));
    }
}

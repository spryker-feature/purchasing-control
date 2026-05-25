<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Yves\Kernel\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterListController extends AbstractPurchasingControlController
{
    protected const string REQUEST_PARAM_PAGE = 'page';

    public function indexAction(Request $request): View
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();
        $idCompany = $companyUserTransfer->getFkCompanyOrFail();

        $form = $this->getFactory()->createCostCenterSearchForm($idCompany);

        $costCenterCriteriaTransfer = $this->getFactory()
            ->createCostCenterSearchFormHandler()
            ->buildCostCenterCriteriaTransfer($request, $form, $idCompany);

        $costCenterCriteriaTransfer->setPagination($this->buildPaginationTransfer($request));

        $costCenterCollectionTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->getCostCenterCollection($costCenterCriteriaTransfer);

        return $this->view(
            [
                'costCenters' => $costCenterCollectionTransfer->getCostCenters(),
                'pagination' => $costCenterCollectionTransfer->getPagination(),
                'searchForm' => $form->createView(),
            ],
            [],
            '@PurchasingControl/views/list-cost-center/list-cost-center.twig',
        );
    }

    protected function buildPaginationTransfer(Request $request): PaginationTransfer
    {
        return (new PaginationTransfer())
            ->setMaxPerPage($this->getFactory()->getConfig()->getCostCenterListDefaultItemsPerPage())
            ->setPage((int)$request->query->get(static::REQUEST_PARAM_PAGE, 1));
    }
}

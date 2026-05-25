<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Yves\Kernel\View\View;
use SprykerFeature\Yves\PurchasingControl\Plugin\Router\BudgetRouteProviderPlugin;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetUpdateController extends AbstractPurchasingControlController
{
    protected const string GLOSSARY_KEY_BUDGET_UPDATED = 'purchasing_control.budget.success.updated';

    protected const string GLOSSARY_KEY_BUDGET_NOT_FOUND = 'purchasing_control.budget.error.not_found';

    protected const string REQUEST_PARAM_BUDGET_UUID = 'budgetUuid';

    protected const string REQUEST_PARAM_COST_CENTER_UUID = 'costCenterUuid';

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request): View|RedirectResponse
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();

        $budgetUuid = (string)$request->query->get(static::REQUEST_PARAM_BUDGET_UUID);
        $costCenterUuid = (string)$request->query->get(static::REQUEST_PARAM_COST_CENTER_UUID);
        $costCenterTransfer = $this->requireCostCenterForCompany($costCenterUuid, $companyUserTransfer->getFkCompanyOrFail());

        $budgetTransfer = $this->getFactory()
            ->createBudgetReader()
            ->findBudget($budgetUuid, $costCenterUuid, $companyUserTransfer->getFkCompanyOrFail());

        if (!$budgetTransfer) {
            throw new NotFoundHttpException(static::GLOSSARY_KEY_BUDGET_NOT_FOUND);
        }

        $form = $this->getFactory()
            ->createBudgetForm($budgetTransfer, $this->getFactory()->createBudgetFormDataProvider()->getOptions())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleFormSubmission($form, $costCenterTransfer, $companyUserTransfer);
        }

        return $this->view(
            [
                'form' => $form->createView(),
                'costCenter' => $costCenterTransfer,
            ],
            [],
            '@PurchasingControl/views/update-budget/update-budget.twig',
        );
    }

    protected function handleFormSubmission(
        FormInterface $form,
        CostCenterTransfer $costCenterTransfer,
        CompanyUserTransfer $companyUserTransfer,
    ): View|RedirectResponse {
        /** @var \Generated\Shared\Transfer\BudgetTransfer $budgetTransfer */
        $budgetTransfer = $form->getData();

        $budgetCollectionResponseTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->updateBudgetCollection(
                (new BudgetCollectionRequestTransfer())
                    ->addBudget($budgetTransfer)
                    ->setIsTransactional(true)
                    ->setCustomer(
                        (new CustomerTransfer())->setCompanyUserTransfer($companyUserTransfer),
                    ),
            );

        if (!$budgetCollectionResponseTransfer->getErrors()->count()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_BUDGET_UPDATED);

            return $this->redirectResponseInternal(
                BudgetRouteProviderPlugin::ROUTE_NAME_BUDGET_LIST,
                [static::REQUEST_PARAM_COST_CENTER_UUID => $costCenterTransfer->getUuidOrFail()],
            );
        }

        $this->addErrorMessages($budgetCollectionResponseTransfer->getErrors());

        return $this->view(
            [
                'form' => $form->createView(),
                'costCenter' => $costCenterTransfer,
            ],
            [],
            '@PurchasingControl/views/update-budget/update-budget.twig',
        );
    }
}

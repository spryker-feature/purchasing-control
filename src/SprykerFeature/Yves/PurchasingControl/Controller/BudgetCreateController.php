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

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class BudgetCreateController extends AbstractPurchasingControlController
{
    protected const string GLOSSARY_KEY_BUDGET_CREATED = 'purchasing_control.budget.success.created';

    protected const string REQUEST_PARAM_COST_CENTER_UUID = 'costCenterUuid';

    public function indexAction(Request $request): View|RedirectResponse
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();

        $costCenterUuid = (string)$request->query->get(static::REQUEST_PARAM_COST_CENTER_UUID);
        $costCenterTransfer = $this->requireCostCenterForCompany($costCenterUuid, $companyUserTransfer->getFkCompanyOrFail());

        $form = $this->getFactory()
            ->createBudgetForm(null, $this->getFactory()->createBudgetFormDataProvider()->getOptions())
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
            '@PurchasingControl/views/create-budget/create-budget.twig',
        );
    }

    protected function handleFormSubmission(
        FormInterface $form,
        CostCenterTransfer $costCenterTransfer,
        CompanyUserTransfer $companyUserTransfer,
    ): View|RedirectResponse {
        /** @var \Generated\Shared\Transfer\BudgetTransfer $budgetTransfer */
        $budgetTransfer = $form->getData();
        $budgetTransfer->setIdCostCenter($costCenterTransfer->getIdCostCenterOrFail());

        $budgetCollectionResponseTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->createBudgetCollection(
                (new BudgetCollectionRequestTransfer())
                    ->addBudget($budgetTransfer)
                    ->setIsTransactional(true)
                    ->setCustomer(
                        (new CustomerTransfer())->setCompanyUserTransfer($companyUserTransfer),
                    ),
            );

        if (!$budgetCollectionResponseTransfer->getErrors()->count()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_BUDGET_CREATED);

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
            '@PurchasingControl/views/create-budget/create-budget.twig',
        );
    }
}

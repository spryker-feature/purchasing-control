<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Yves\Kernel\View\View;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterForm;
use SprykerFeature\Yves\PurchasingControl\Plugin\Router\CostCenterRouteProviderPlugin;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class CostCenterCreateController extends AbstractPurchasingControlController
{
    protected const string GLOSSARY_KEY_COST_CENTER_CREATED = 'purchasing_control.cost_center.success.created';

    public function indexAction(Request $request): View|RedirectResponse
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();

        $form = $this->getFactory()
            ->createCostCenterForm(null, $this->getFactory()->createCostCenterFormDataProvider()->getOptions($companyUserTransfer->getFkCompanyOrFail()))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleFormSubmission($form, $companyUserTransfer);
        }

        return $this->view(
            ['form' => $form->createView()],
            [],
            '@PurchasingControl/views/create-cost-center/create-cost-center.twig',
        );
    }

    protected function handleFormSubmission(FormInterface $form, CompanyUserTransfer $companyUserTransfer): View|RedirectResponse
    {
        $costCenterCollectionResponseTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->createCostCenterCollection($this->buildCostCenterCollectionRequestTransfer($form, $companyUserTransfer));

        if (!$costCenterCollectionResponseTransfer->getErrors()->count()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_COST_CENTER_CREATED);

            return $this->redirectResponseInternal(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_LIST);
        }

        $this->addErrorMessages($costCenterCollectionResponseTransfer->getErrors());

        return $this->view(
            ['form' => $form->createView()],
            [],
            '@PurchasingControl/views/create-cost-center/create-cost-center.twig',
        );
    }

    protected function buildCostCenterCollectionRequestTransfer(
        FormInterface $form,
        CompanyUserTransfer $companyUserTransfer
    ): CostCenterCollectionRequestTransfer {
        /** @var \Generated\Shared\Transfer\CostCenterTransfer $costCenterTransfer */
        $costCenterTransfer = $form->getData();

        /** @var array<int> $idsCompanyBusinessUnits */
        $idsCompanyBusinessUnits = $form->get(CostCenterForm::FIELD_COMPANY_BUSINESS_UNIT_IDS)->getData();
        $costCenterTransfer->setCompanyBusinessUnitIds($idsCompanyBusinessUnits);

        return (new CostCenterCollectionRequestTransfer())
            ->addCostCenter($costCenterTransfer)
            ->setIsTransactional(true)
            ->setCustomer(
                (new CustomerTransfer())->setCompanyUserTransfer($companyUserTransfer),
            );
    }
}

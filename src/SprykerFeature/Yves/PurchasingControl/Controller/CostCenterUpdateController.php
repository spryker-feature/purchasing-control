<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Yves\Kernel\View\View;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterForm;
use SprykerFeature\Yves\PurchasingControl\Plugin\Router\CostCenterRouteProviderPlugin;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class CostCenterUpdateController extends AbstractPurchasingControlController
{
    protected const string GLOSSARY_KEY_COST_CENTER_UPDATED = 'purchasing_control.cost_center.success.updated';

    protected const string GLOSSARY_KEY_COST_CENTER_NOT_FOUND = 'purchasing_control.cost_center.error.not_found';

    protected const string REQUEST_PARAM_COST_CENTER_UUID = 'costCenterUuid';

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request): View|RedirectResponse
    {
        $companyUserTransfer = $this->requireAuthorizedCompanyUser();

        $costCenterUuid = (string)$request->query->get(static::REQUEST_PARAM_COST_CENTER_UUID);
        $costCenterTransfer = $this->getFactory()->createCostCenterReader()->findCostCenter($costCenterUuid, $companyUserTransfer->getFkCompanyOrFail());

        if (!$costCenterTransfer) {
            throw new NotFoundHttpException(static::GLOSSARY_KEY_COST_CENTER_NOT_FOUND);
        }

        $formOptions = $this->getFactory()->createCostCenterFormDataProvider()->getOptions($companyUserTransfer->getFkCompanyOrFail());
        $formOptions[CostCenterForm::OPTION_SELECTED_BUSINESS_UNIT_IDS] = $costCenterTransfer->getCompanyBusinessUnitIds();

        $form = $this->getFactory()
            ->createCostCenterForm($costCenterTransfer, $formOptions)
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
            '@PurchasingControl/views/update-cost-center/update-cost-center.twig',
        );
    }

    protected function handleFormSubmission(
        FormInterface $form,
        CostCenterTransfer $costCenterTransfer,
        CompanyUserTransfer $companyUserTransfer
    ): View|RedirectResponse {
        $costCenterCollectionResponseTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->updateCostCenterCollection($this->buildCollectionRequest($form, $companyUserTransfer));

        if (!$costCenterCollectionResponseTransfer->getErrors()->count()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_COST_CENTER_UPDATED);

            return $this->redirectResponseInternal(CostCenterRouteProviderPlugin::ROUTE_NAME_COST_CENTER_LIST);
        }

        $this->addErrorMessages($costCenterCollectionResponseTransfer->getErrors());

        return $this->view(
            [
                'form' => $form->createView(),
                'costCenter' => $costCenterTransfer,
            ],
            [],
            '@PurchasingControl/views/update-cost-center/update-cost-center.twig',
        );
    }

    protected function buildCollectionRequest(FormInterface $form, CompanyUserTransfer $companyUserTransfer): CostCenterCollectionRequestTransfer
    {
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

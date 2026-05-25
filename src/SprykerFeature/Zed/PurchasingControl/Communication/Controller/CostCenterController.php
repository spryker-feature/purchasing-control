<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\CostCenterForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterController extends AbstractPurchasingControlController
{
    public const string PARAM_ID_COST_CENTER = 'id-cost-center';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\CostCenterController::indexAction()
     *
     * @var string
     */
    protected const string URL_COST_CENTER_LIST = '/purchasing-control/cost-center/index';

    protected const string MESSAGE_COST_CENTER_CREATED = 'Cost center created successfully.';

    protected const string MESSAGE_COST_CENTER_UPDATED = 'Cost center updated successfully.';

    protected const string MESSAGE_COST_CENTER_NOT_FOUND = 'Cost center not found.';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(): array
    {
        $costCenterTable = $this->getFactory()->createCostCenterTable();

        return $this->viewResponse([
            'costCenterTable' => $costCenterTable->render(),
        ]);
    }

    public function tableAction(): JsonResponse
    {
        $costCenterTable = $this->getFactory()->createCostCenterTable();

        return $this->jsonResponse($costCenterTable->fetchData());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function createAction(Request $request): RedirectResponse|array
    {
        $formOptions = $this->getFactory()->createCostCenterFormDataProvider()->getOptions();

        if ($request->get(CostCenterForm::FORM_NAME)) {
            $formOptions = $this->getFactory()->createCostCenterFormDataProvider()->expandOptionsWithSubmittedData(
                $formOptions,
                $request->get(CostCenterForm::FORM_NAME),
            );
        }

        $costCenterForm = $this->getFactory()->createCostCenterForm(new CostCenterTransfer(), $formOptions);
        $costCenterForm->handleRequest($request);

        if (!$costCenterForm->isSubmitted() || !$costCenterForm->isValid()) {
            return $this->viewResponse([
                'costCenterForm' => $costCenterForm->createView(),
            ]);
        }

        $costCenterCollectionResponseTransfer = $this->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())->addCostCenter($costCenterForm->getData()),
        );

        if ($costCenterCollectionResponseTransfer->getErrors()->count() > 0) {
            $this->addTranslatedErrorMessages($costCenterCollectionResponseTransfer->getErrors());

            return $this->viewResponse([
                'costCenterForm' => $costCenterForm->createView(),
            ]);
        }

        $this->addSuccessMessage(static::MESSAGE_COST_CENTER_CREATED);

        return $this->redirectResponse(static::URL_COST_CENTER_LIST);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function editAction(Request $request): RedirectResponse|array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));

        $costCenterCollectionTransfer = $this->getFacade()->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCostCenter($idCostCenter),
            ),
        );

        if ($costCenterCollectionTransfer->getCostCenters()->count() === 0) {
            $this->addErrorMessage(static::MESSAGE_COST_CENTER_NOT_FOUND);

            return $this->redirectResponse(static::URL_COST_CENTER_LIST);
        }

        $existingCostCenterTransfer = $costCenterCollectionTransfer->getCostCenters()->getIterator()->current();

        $formOptions = $this->getFactory()->createCostCenterFormDataProvider()->getOptions($existingCostCenterTransfer);
        if ($request->get(CostCenterForm::FORM_NAME)) {
            $formOptions = $this->getFactory()->createCostCenterFormDataProvider()->expandOptionsWithSubmittedData(
                $formOptions,
                $request->get(CostCenterForm::FORM_NAME),
            );
        }

        $costCenterForm = $this->getFactory()->createCostCenterForm($existingCostCenterTransfer, $formOptions);
        $costCenterForm->handleRequest($request);

        if (!$costCenterForm->isSubmitted() || !$costCenterForm->isValid()) {
            return $this->viewResponse([
                'costCenterForm' => $costCenterForm->createView(),
                'idCostCenter' => $idCostCenter,
            ]);
        }

        $costCenterTransfer = $costCenterForm->getData();
        $costCenterTransfer->setIdCostCenter($idCostCenter);

        $costCenterCollectionResponseTransfer = $this->getFacade()->updateCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())->addCostCenter($costCenterTransfer),
        );

        if ($costCenterCollectionResponseTransfer->getErrors()->count() > 0) {
            $this->addTranslatedErrorMessages($costCenterCollectionResponseTransfer->getErrors());

            return $this->viewResponse([
                'costCenterForm' => $costCenterForm->createView(),
                'idCostCenter' => $idCostCenter,
            ]);
        }

        $this->addSuccessMessage(static::MESSAGE_COST_CENTER_UPDATED);

        return $this->redirectResponse(static::URL_COST_CENTER_LIST);
    }
}

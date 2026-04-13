<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class CostCenterController extends AbstractController
{
    public const PARAM_ID_COST_CENTER = 'id-cost-center';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\CostCenterController::indexAction()
     *
     * @var string
     */
    protected const URL_COST_CENTER_LIST = '/purchasing-control/cost-center/index';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(Request $request): array
    {
        $costCenterTable = $this->getFactory()->createCostCenterTable();

        return $this->viewResponse([
            'costCenterTable' => $costCenterTable->render(),
        ]);
    }

    public function tableAction(Request $request): JsonResponse
    {
        $costCenterTable = $this->getFactory()->createCostCenterTable();

        return $this->jsonResponse($costCenterTable->fetchData());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function createAction(Request $request): RedirectResponse|array
    {
        $costCenterForm = $this->getFactory()->createCostCenterForm();
        $costCenterForm->handleRequest($request);

        if ($costCenterForm->isSubmitted() && $costCenterForm->isValid()) {
            $costCenterTransfer = (new CostCenterTransfer())->fromArray($costCenterForm->getData(), true);
            $costCenterResponseTransfer = $this->getFacade()->createCostCenter($costCenterTransfer);

            if ($costCenterResponseTransfer->getIsSuccessful()) {
                $this->addSuccessMessage('Cost center created successfully.');

                return $this->redirectResponse(static::URL_COST_CENTER_LIST);
            }

            foreach ($costCenterResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getValueOrFail());
            }
        }

        return $this->viewResponse([
            'costCenterForm' => $costCenterForm->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function editAction(Request $request): RedirectResponse|array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $existingCostCenter = $this->getFacade()->getCostCenterById($idCostCenter);

        if ($existingCostCenter->getIdCostCenter() === null) {
            $this->addErrorMessage('Cost center not found.');

            return $this->redirectResponse(static::URL_COST_CENTER_LIST);
        }

        $costCenterForm = $this->getFactory()->createCostCenterForm($existingCostCenter->toArray(true, true));
        $costCenterForm->handleRequest($request);

        if ($costCenterForm->isSubmitted() && $costCenterForm->isValid()) {
            $costCenterTransfer = (new CostCenterTransfer())->fromArray($costCenterForm->getData(), true);
            $costCenterTransfer->setIdCostCenter($idCostCenter);

            $costCenterResponseTransfer = $this->getFacade()->updateCostCenter($costCenterTransfer);

            if ($costCenterResponseTransfer->getIsSuccessful()) {
                $this->addSuccessMessage('Cost center updated successfully.');

                return $this->redirectResponse(static::URL_COST_CENTER_LIST);
            }

            foreach ($costCenterResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getValueOrFail());
            }
        }

        return $this->viewResponse([
            'costCenterForm' => $costCenterForm->createView(),
            'idCostCenter' => $idCostCenter,
        ]);
    }
}

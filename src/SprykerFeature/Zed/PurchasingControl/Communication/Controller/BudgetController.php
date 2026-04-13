<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\BudgetTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\BudgetForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class BudgetController extends AbstractController
{
    public const PARAM_ID_COST_CENTER = 'id-cost-center';

    public const PARAM_ID_BUDGET = 'id-budget';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::indexAction()
     *
     * @var string
     */
    protected const URL_BUDGET_LIST = '/purchasing-control/budget/index';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(Request $request): array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $budgetTable = $this->getFactory()->createBudgetTable($idCostCenter);
        $costCenter = $this->getFacade()->getCostCenterById($idCostCenter);

        return $this->viewResponse([
            'budgetTable' => $budgetTable->render(),
            'idCostCenter' => $idCostCenter,
            'costCenterName' => $costCenter->getName() ?? sprintf('#%d', $idCostCenter),
        ]);
    }

    public function tableAction(Request $request): JsonResponse
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $budgetTable = $this->getFactory()->createBudgetTable($idCostCenter);

        return $this->jsonResponse($budgetTable->fetchData());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function createAction(Request $request): RedirectResponse|array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $costCenter = $this->getFacade()->getCostCenterById($idCostCenter);

        $budgetForm = $this->getFactory()->createBudgetForm();
        $budgetForm->handleRequest($request);

        if ($budgetForm->isSubmitted() && $budgetForm->isValid()) {
            $formData = $budgetForm->getData();
            $budgetTransfer = (new BudgetTransfer())->fromArray($formData, true);
            $budgetTransfer
                ->setIdCostCenter($idCostCenter)
                ->setAmount((int)round($formData[BudgetForm::FIELD_AMOUNT] * 100));

            $budgetResponseTransfer = $this->getFacade()->createBudget($budgetTransfer);

            if ($budgetResponseTransfer->getIsSuccessful()) {
                $this->addSuccessMessage('Budget created successfully.');

                return $this->redirectResponse(
                    sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
                );
            }

            foreach ($budgetResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getValueOrFail());
            }
        }

        return $this->viewResponse([
            'budgetForm' => $budgetForm->createView(),
            'idCostCenter' => $idCostCenter,
            'costCenterName' => $costCenter->getName() ?? sprintf('#%d', $idCostCenter),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function editAction(Request $request): RedirectResponse|array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $idBudget = $this->castId($request->query->get(static::PARAM_ID_BUDGET));

        $existingBudget = $this->getFacade()->getBudgetById($idBudget);

        if ($existingBudget->getIdBudget() === null) {
            $this->addErrorMessage('Budget not found.');

            return $this->redirectResponse(
                sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
            );
        }

        $costCenter = $this->getFacade()->getCostCenterById($idCostCenter);

        $formData = $existingBudget->toArray(true, true);
        // Convert stored cents to decimal for display
        $formData[BudgetForm::FIELD_AMOUNT] = $existingBudget->getAmountOrFail() / 100;

        $budgetForm = $this->getFactory()->createBudgetForm($formData);
        $budgetForm->handleRequest($request);

        if ($budgetForm->isSubmitted() && $budgetForm->isValid()) {
            $submittedData = $budgetForm->getData();
            $budgetTransfer = (new BudgetTransfer())->fromArray($submittedData, true);
            $budgetTransfer
                ->setIdBudget($idBudget)
                ->setIdCostCenter($idCostCenter)
                ->setAmount((int)round($submittedData[BudgetForm::FIELD_AMOUNT] * 100));

            $budgetResponseTransfer = $this->getFacade()->updateBudget($budgetTransfer);

            if ($budgetResponseTransfer->getIsSuccessful()) {
                $this->addSuccessMessage('Budget updated successfully.');

                return $this->redirectResponse(
                    sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
                );
            }

            foreach ($budgetResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getValueOrFail());
            }
        }

        return $this->viewResponse([
            'budgetForm' => $budgetForm->createView(),
            'idCostCenter' => $idCostCenter,
            'idBudget' => $idBudget,
            'costCenterName' => $costCenter->getName() ?? sprintf('#%d', $idCostCenter),
        ]);
    }
}

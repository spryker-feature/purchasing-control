<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class BudgetController extends AbstractPurchasingControlController
{
    public const string PARAM_ID_COST_CENTER = 'id-cost-center';

    public const string PARAM_ID_BUDGET = 'id-budget';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::indexAction()
     *
     * @var string
     */
    protected const string URL_BUDGET_LIST = '/purchasing-control/budget/index';

    protected const string MESSAGE_BUDGET_CREATED = 'Budget created successfully.';

    protected const string MESSAGE_BUDGET_UPDATED = 'Budget updated successfully.';

    protected const string MESSAGE_BUDGET_NOT_FOUND = 'Budget not found.';

    /**
     * @return array<string, mixed>
     */
    public function indexAction(Request $request): array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));

        return $this->viewResponse([
            'budgetTable' => $this->getFactory()->createBudgetTable($idCostCenter)->render(),
            'idCostCenter' => $idCostCenter,
            'costCenterName' => $this->getFactory()->createCostCenterReader()->findCostCenterName($idCostCenter),
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
        $costCenterName = $this->getFactory()->createCostCenterReader()->findCostCenterName($idCostCenter);

        $budgetForm = $this->getFactory()->createBudgetForm();
        $budgetForm->handleRequest($request);

        if (!$budgetForm->isSubmitted() || !$budgetForm->isValid()) {
            return $this->viewResponse([
                'budgetForm' => $budgetForm->createView(),
                'idCostCenter' => $idCostCenter,
                'costCenterName' => $costCenterName,
            ]);
        }

        $budgetTransfer = (new BudgetTransfer())->fromArray($budgetForm->getData(), true);
        $budgetTransfer->setIdCostCenter($idCostCenter);

        $responseTransfer = $this->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())->addBudget($budgetTransfer),
        );

        if ($responseTransfer->getErrors()->count() > 0) {
            $this->addTranslatedErrorMessages($responseTransfer->getErrors());

            return $this->viewResponse([
                'budgetForm' => $budgetForm->createView(),
                'idCostCenter' => $idCostCenter,
                'costCenterName' => $costCenterName,
            ]);
        }

        $this->addSuccessMessage(static::MESSAGE_BUDGET_CREATED);

        return $this->redirectResponse(
            sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array<string, mixed>
     */
    public function editAction(Request $request): RedirectResponse|array
    {
        $idCostCenter = $this->castId($request->query->get(static::PARAM_ID_COST_CENTER));
        $idBudget = $this->castId($request->query->get(static::PARAM_ID_BUDGET));

        $budgetCollectionTransfer = $this->getFacade()->getBudgetCollection(
            (new BudgetCriteriaTransfer())->setBudgetConditions(
                (new BudgetConditionsTransfer())->addIdBudget($idBudget),
            ),
        );

        if ($budgetCollectionTransfer->getBudgets()->count() === 0) {
            $this->addErrorMessage(static::MESSAGE_BUDGET_NOT_FOUND);

            return $this->redirectResponse(
                sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
            );
        }

        $costCenterName = $this->getFactory()->createCostCenterReader()->findCostCenterName($idCostCenter);
        $existingBudgetTransfer = $budgetCollectionTransfer->getBudgets()->getIterator()->current();

        $budgetForm = $this->getFactory()->createBudgetForm($existingBudgetTransfer->toArray(true, true));
        $budgetForm->handleRequest($request);

        if (!$budgetForm->isSubmitted() || !$budgetForm->isValid()) {
            return $this->viewResponse([
                'budgetForm' => $budgetForm->createView(),
                'idCostCenter' => $idCostCenter,
                'idBudget' => $idBudget,
                'costCenterName' => $costCenterName,
            ]);
        }

        $budgetTransfer = (new BudgetTransfer())->fromArray($budgetForm->getData(), true);
        $budgetTransfer
            ->setIdBudget($idBudget)
            ->setIdCostCenter($idCostCenter);

        $responseTransfer = $this->getFacade()->updateBudgetCollection(
            (new BudgetCollectionRequestTransfer())->addBudget($budgetTransfer),
        );

        if ($responseTransfer->getErrors()->count() > 0) {
            $this->addTranslatedErrorMessages($responseTransfer->getErrors());

            return $this->viewResponse([
                'budgetForm' => $budgetForm->createView(),
                'idCostCenter' => $idCostCenter,
                'idBudget' => $idBudget,
                'costCenterName' => $costCenterName,
            ]);
        }

        $this->addSuccessMessage(static::MESSAGE_BUDGET_UPDATED);

        return $this->redirectResponse(
            sprintf('%s?%s=%d', static::URL_BUDGET_LIST, static::PARAM_ID_COST_CENTER, $idCostCenter),
        );
    }
}

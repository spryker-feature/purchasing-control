<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use Generated\Shared\Transfer\BudgetConditionsTransfer;
use Generated\Shared\Transfer\BudgetCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
class BudgetSuggestController extends AbstractPurchasingControlController
{
    protected const string PARAM_COST_CENTER_IDS = 'idsCostCenter';

    protected const string KEY_RESULTS = 'results';

    public function indexAction(Request $request): JsonResponse
    {
        $budgetConditionsTransfer = new BudgetConditionsTransfer();
        foreach ($this->parseCostCenterIds($request) as $idCostCenter) {
            $budgetConditionsTransfer->addIdCostCenter($idCostCenter);
        }

        $budgetCriteriaTransfer = (new BudgetCriteriaTransfer())
            ->setBudgetConditions($budgetConditionsTransfer)
            ->setPagination(
                (new PaginationTransfer())->setLimit($this->getFactory()->getConfig()->getBudgetFilterLimit()),
            );

        $results = [];

        foreach ($this->getFacade()->getBudgetCollection($budgetCriteriaTransfer)->getBudgets() as $budgetTransfer) {
            $results[] = [
                'id' => $budgetTransfer->getIdBudgetOrFail(),
                'text' => $budgetTransfer->getNameOrFail(),
            ];
        }

        return $this->jsonResponse([static::KEY_RESULTS => $results]);
    }

    /**
     * @return array<int>
     */
    protected function parseCostCenterIds(Request $request): array
    {
        $rawValue = $request->query->get(static::PARAM_COST_CENTER_IDS);

        if (!$rawValue) {
            return [];
        }

        return array_values(
            array_filter(
                array_map('intval', explode(',', (string)$rawValue)),
            ),
        );
    }
}

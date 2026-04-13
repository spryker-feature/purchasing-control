<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Spryker\Yves\Kernel\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterController extends AbstractController
{
    protected const string ROUTE_CHECKOUT_SUMMARY = 'checkout-summary';

    public function updateQuoteAction(Request $request): RedirectResponse
    {
        $idCostCenter = (int)$request->request->get('idCostCenter');
        $idBudget = (int)$request->request->get('idBudget');

        $customerTransfer = $this->getFactory()->getCustomerClient()->getCustomer();

        if (!$customerTransfer || !$customerTransfer->getCompanyUserTransfer()) {
            return $this->redirectResponseInternal(static::ROUTE_CHECKOUT_SUMMARY);
        }

        $idCompanyBusinessUnit = $customerTransfer->getCompanyUserTransfer()->getFkCompanyBusinessUnitOrFail();

        $quoteTransfer = $this->getFactory()->getQuoteClient()->getQuote();
        $currencyIsoCode = $quoteTransfer->getCurrency() ? $quoteTransfer->getCurrencyOrFail()->getCode() ?? '' : '';

        // Security check: ensure cost center belongs to the current user's business unit and has an active budget for the quote currency
        $costCenterCollection = $this->getFactory()
            ->getPurchasingControlClient()
            ->getActiveCostCentersForCompanyBusinessUnit($idCompanyBusinessUnit, $currencyIsoCode);

        $isAuthorized = false;
        foreach ($costCenterCollection->getCostCenters() as $costCenter) {
            if ($costCenter->getIdCostCenter() === $idCostCenter) {
                $isAuthorized = true;

                break;
            }
        }

        if (!$isAuthorized) {
            return $this->redirectResponseInternal(static::ROUTE_CHECKOUT_SUMMARY);
        }

        if ($quoteTransfer->getIdCostCenter() !== $idCostCenter) {
            $idBudget = null;
        }

        if (!$idBudget && $idCostCenter > 0) {
            $idBudget = $this->resolveFirstActiveBudgetId($idCostCenter, $currencyIsoCode);
        }

        $quoteTransfer->setIdCostCenter($idCostCenter ?: null);
        $quoteTransfer->setIdBudget($idBudget ?: null);

        $this->getFactory()->getQuoteClient()->setQuote($quoteTransfer);

        return $this->redirectResponseInternal(static::ROUTE_CHECKOUT_SUMMARY);
    }

    protected function resolveFirstActiveBudgetId(int $idCostCenter, string $currencyIsoCode): int
    {
        $budgetCollection = $this->getFactory()
            ->getPurchasingControlClient()
            ->getActiveBudgetsForCostCenter($idCostCenter, $currencyIsoCode);

        $firstBudget = $budgetCollection->getBudgets()->getIterator()->current();

        return $firstBudget ? $firstBudget->getIdBudgetOrFail() : 0;
    }
}

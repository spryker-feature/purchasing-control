<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\Controller\AbstractController;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
class CostCenterController extends AbstractController
{
    /**
     * @uses \SprykerShop\Yves\CheckoutPage\Controller\CheckoutController::summaryAction()
     */
    protected const string ROUTE_CHECKOUT_SUMMARY = 'checkout-summary';

    public function updateQuoteAction(Request $request): RedirectResponse
    {
        $response = $this->redirectResponseInternal(static::ROUTE_CHECKOUT_SUMMARY);

        $customerTransfer = $this->getFactory()->getCustomerClient()->getCustomer();
        if (!$customerTransfer?->getCompanyUserTransfer()) {
            return $response;
        }

        $quoteTransfer = $this->getFactory()->getQuoteClient()->getQuote();
        $form = $this->getFactory()->createCostCenterSelectorForm($quoteTransfer);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $response;
        }

        if (!$form->isValid()) {
            /** @var \Symfony\Component\Form\FormError $formError */
            foreach ($form->getErrors(true) as $formError) {
                $this->addErrorMessage($formError->getMessage());
            }

            return $response;
        }

        $responseTransfer = $this->getFactory()
            ->getPurchasingControlClient()
            ->updateQuoteCostCenter($this->buildUpdateRequest($quoteTransfer, $form));

        if (!$responseTransfer->getIsSuccessful()) {
            $this->addErrorMessages($responseTransfer);

            return $response;
        }

        $this->updateSessionQuote($quoteTransfer, $responseTransfer);

        return $response;
    }

    protected function buildUpdateRequest(QuoteTransfer $quoteTransfer, FormInterface $form): CostCenterQuoteUpdateRequestTransfer
    {
        $formData = $form->getData();

        return (new CostCenterQuoteUpdateRequestTransfer())
            ->setIdQuote($quoteTransfer->getIdQuoteOrFail())
            ->setIdCostCenter($formData[CostCenterSelectorForm::FIELD_ID_COST_CENTER])
            ->setIdBudget($quoteTransfer->getIdCostCenter() !== $formData[CostCenterSelectorForm::FIELD_ID_COST_CENTER] ? null : $formData[CostCenterSelectorForm::FIELD_ID_BUDGET])
            ->setCustomer($this->getFactory()->getCustomerClient()->getCustomer());
    }

    protected function addErrorMessages(CostCenterQuoteUpdateResponseTransfer $responseTransfer): void
    {
        foreach ($responseTransfer->getErrors() as $errorTransfer) {
            $this->addErrorMessage($errorTransfer->getMessageOrFail());
        }
    }

    protected function updateSessionQuote(QuoteTransfer $quoteTransfer, CostCenterQuoteUpdateResponseTransfer $responseTransfer): void
    {
        $quoteTransfer->fromArray($responseTransfer->getQuoteOrFail()->modifiedToArray(), true);
        $this->getFactory()->getQuoteClient()->setQuote($quoteTransfer);
    }
}

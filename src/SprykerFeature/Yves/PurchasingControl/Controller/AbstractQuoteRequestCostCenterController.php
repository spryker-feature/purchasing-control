<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Spryker\Yves\Kernel\Controller\AbstractController;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use SprykerFeature\Yves\PurchasingControl\Reader\QuoteRequestReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Writer\QuoteRequestCostCenterWriterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
abstract class AbstractQuoteRequestCostCenterController extends AbstractController
{
    /**
     * @uses \SprykerShop\Yves\QuoteRequestPage\Plugin\Router\QuoteRequestPageRouteProviderPlugin::PARAM_QUOTE_REQUEST_REFERENCE
     * @uses \SprykerShop\Yves\QuoteRequestAgentPage\Plugin\Router\QuoteRequestAgentPageRouteProviderPlugin::PARAM_QUOTE_REQUEST_REFERENCE
     */
    protected const string PARAM_QUOTE_REQUEST_REFERENCE = 'quoteRequestReference';

    protected const string GLOSSARY_KEY_QUOTE_REQUEST_UPDATED = 'purchasing_control.quote_request.cost_center_updated';

    abstract protected function createQuoteRequestReader(): QuoteRequestReaderInterface;

    abstract protected function createQuoteRequestCostCenterWriter(): QuoteRequestCostCenterWriterInterface;

    public function updateQuoteRequestAction(string $quoteRequestReference, string $redirectRoute, Request $request): RedirectResponse
    {
        $this->executeUpdateQuoteRequestAction($quoteRequestReference, $request);

        return $this->redirectResponseInternal($redirectRoute, [
            static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
        ]);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function executeUpdateQuoteRequestAction(string $quoteRequestReference, Request $request): void
    {
        $quoteRequestResponseTransfer = $this->createQuoteRequestReader()->findQuoteRequest($quoteRequestReference);

        if (!$quoteRequestResponseTransfer->getIsSuccessful()) {
            // Rendered by the error page, which shares the notification area with every other page.
            $this->addResponseErrorMessages($quoteRequestResponseTransfer);

            throw new NotFoundHttpException();
        }

        $quoteRequestTransfer = $quoteRequestResponseTransfer->getQuoteRequestOrFail();
        $quoteTransfer = $quoteRequestTransfer->getLatestVersionOrFail()->getQuoteOrFail();

        $form = $this->getFactory()->createCostCenterSelectorForm(
            $quoteTransfer,
            $quoteRequestTransfer->getCompanyUser()?->getFkCompanyBusinessUnit(),
        );
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return;
        }

        if (!$form->isValid()) {
            $this->addFormErrorMessages($form);

            return;
        }

        $formData = $form->getData();

        $quoteRequestResponseTransfer = $this->createQuoteRequestCostCenterWriter()->updateCostCenter(
            $quoteRequestTransfer,
            $formData[CostCenterSelectorForm::FIELD_ID_COST_CENTER],
            $formData[CostCenterSelectorForm::FIELD_ID_BUDGET],
        );

        if (!$quoteRequestResponseTransfer->getIsSuccessful()) {
            $this->addResponseErrorMessages($quoteRequestResponseTransfer);

            return;
        }

        $this->addSuccessMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_UPDATED);
    }

    protected function addFormErrorMessages(FormInterface $form): void
    {
        /** @var \Symfony\Component\Form\FormError $formError */
        foreach ($form->getErrors(true) as $formError) {
            $this->addErrorMessage($formError->getMessage());
        }
    }

    protected function addResponseErrorMessages(QuoteRequestResponseTransfer $quoteRequestResponseTransfer): void
    {
        foreach ($quoteRequestResponseTransfer->getMessages() as $messageTransfer) {
            $this->addErrorMessage($messageTransfer->getValueOrFail());
        }
    }
}

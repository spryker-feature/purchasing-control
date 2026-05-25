<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Controller;

use ArrayObject;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 */
abstract class AbstractPurchasingControlController extends AbstractController
{
    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ErrorTransfer> $errorTransfers
     */
    protected function addTranslatedErrorMessages(ArrayObject $errorTransfers): void
    {
        $glossaryKeys = array_map(
            fn (ErrorTransfer $errorTransfer) => $errorTransfer->getMessageOrFail(),
            iterator_to_array($errorTransfers),
        );

        $localeTransfer = $this->getFactory()->getLocaleFacade()->getCurrentLocale();
        $translationTransfers = $this->getFactory()->getGlossaryFacade()->getTranslationsByGlossaryKeysAndLocaleTransfers(
            $glossaryKeys,
            [$localeTransfer],
        );

        $keyToTranslation = [];
        foreach ($translationTransfers as $translationTransfer) {
            $keyToTranslation[$translationTransfer->getGlossaryKeyOrFail()->getKeyOrFail()] = (string)$translationTransfer->getValue();
        }

        foreach ($glossaryKeys as $key) {
            $this->addErrorMessage($keyToTranslation[$key] ?? $key);
        }
    }
}

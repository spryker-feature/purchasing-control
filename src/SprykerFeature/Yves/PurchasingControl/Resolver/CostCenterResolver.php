<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Resolver;

use ArrayObject;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Customer\CustomerClientInterface;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;

class CostCenterResolver implements CostCenterResolverInterface
{
    public function __construct(
        protected readonly CustomerClientInterface $customerClient,
        protected readonly CostCenterReaderInterface $costCenterReader,
    ) {
    }

    public function resolveCostCenters(QuoteTransfer $quoteTransfer): ArrayObject
    {
        $customerTransfer = $this->customerClient->getCustomer();

        if (!$customerTransfer || !$customerTransfer->getCompanyUserTransfer()) {
            return new ArrayObject();
        }

        $currencyCode = $quoteTransfer->getCurrency()?->getCode();

        if ($currencyCode === null) {
            return new ArrayObject();
        }

        return $this->costCenterReader
            ->getActiveCostCentersForCompanyBusinessUnit(
                $customerTransfer->getCompanyUserTransfer()->getFkCompanyBusinessUnitOrFail(),
                $currencyCode,
                $quoteTransfer->getIsLocked() === true,
            )
            ->getCostCenters();
    }

    public function resolveSelectedCostCenter(ArrayObject $costCenterTransfers, ?int $idCostCenter): ?CostCenterTransfer
    {
        if ($costCenterTransfers->count() === 1) {
            return $costCenterTransfers->offsetGet(0);
        }

        if ($idCostCenter === null) {
            return null;
        }

        foreach ($costCenterTransfers as $costCenterTransfer) {
            if ($costCenterTransfer->getIdCostCenter() === $idCostCenter) {
                return $costCenterTransfer;
            }
        }

        return null;
    }
}

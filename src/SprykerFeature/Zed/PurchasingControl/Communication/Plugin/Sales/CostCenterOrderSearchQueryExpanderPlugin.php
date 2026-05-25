<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderQueryExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlBusinessFactory getBusinessFactory()
 */
class CostCenterOrderSearchQueryExpanderPlugin extends AbstractPlugin implements SearchOrderQueryExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns true when any filter field has type `costCenter` or `budget`.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     */
    public function isApplicable(array $filterFieldTransfers): bool
    {
        return $this->getBusinessFactory()->createOrderSearchQueryExpander()->isApplicable($filterFieldTransfers);
    }

    /**
     * {@inheritDoc}
     * - Expands QueryJoinCollectionTransfer with WHERE conditions for `fk_cost_center` and `fk_budget`.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     */
    public function expand(
        array $filterFieldTransfers,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer,
    ): QueryJoinCollectionTransfer {
        return $this->getBusinessFactory()->createOrderSearchQueryExpander()->expandQueryJoinCollection(
            $filterFieldTransfers,
            $queryJoinCollectionTransfer,
        );
    }
}

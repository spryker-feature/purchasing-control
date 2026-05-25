<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Widget;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

class CostCenterOrderDetailWidget extends AbstractWidget
{
    protected const string PARAMETER_COST_CENTER_NAME = 'costCenterName';

    protected const string PARAMETER_BUDGET_NAME = 'budgetName';

    public function __construct(OrderTransfer $orderTransfer)
    {
        $this->addCostCenterNameParameter($orderTransfer);
        $this->addBudgetNameParameter($orderTransfer);
    }

    public static function getName(): string
    {
        return 'CostCenterOrderDetailWidget';
    }

    public static function getTemplate(): string
    {
        return '@PurchasingControl/components/molecules/cost-center-order-detail/cost-center-order-detail.twig';
    }

    protected function addCostCenterNameParameter(OrderTransfer $orderTransfer): void
    {
        $this->addParameter(
            static::PARAMETER_COST_CENTER_NAME,
            $orderTransfer->getCostCenter()?->getName(),
        );
    }

    protected function addBudgetNameParameter(OrderTransfer $orderTransfer): void
    {
        $this->addParameter(
            static::PARAMETER_BUDGET_NAME,
            $orderTransfer->getBudget()?->getName(),
        );
    }
}

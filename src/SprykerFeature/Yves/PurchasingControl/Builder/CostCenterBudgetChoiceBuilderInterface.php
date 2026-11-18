<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Builder;

use Generated\Shared\Transfer\CostCenterCollectionTransfer;

interface CostCenterBudgetChoiceBuilderInterface
{
    /**
     * @return array<string, int>
     */
    public function buildCostCenterChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array;

    /**
     * @return array<string, int>
     */
    public function buildBudgetChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array;

    /**
     * @return array<int, int>
     */
    public function buildBudgetCostCenterMap(CostCenterCollectionTransfer $costCenterCollectionTransfer): array;

    /**
     * @return array<int, array<string, string>>
     */
    public function buildBudgetChoiceAttrs(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        string $currencyIsoCode,
    ): array;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Generated\Shared\Transfer\RecurringScheduleTransfer;
use Symfony\Component\Form\FormBuilderInterface;

interface CostCenterBudgetFormFieldExpanderInterface
{
    public function expandForm(
        FormBuilderInterface $builder,
        ?RecurringScheduleTransfer $recurringScheduleTransfer,
        bool $isSelectionRequired = false
    ): void;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Generated\Shared\Transfer\RecurringScheduleTransfer;
use SprykerFeature\Yves\OrderExperienceManagement\Form\RecurringScheduleEditForm;
use Symfony\Component\Form\FormBuilderInterface;

class CostCenterRecurringScheduleEditFormExpander implements CostCenterRecurringScheduleEditFormExpanderInterface
{
    public function __construct(
        protected readonly CostCenterBudgetFormFieldExpanderInterface $costCenterBudgetFormFieldExpander,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void
    {
        $this->costCenterBudgetFormFieldExpander->expandForm($builder, $this->resolveSchedule($options), true);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function resolveSchedule(array $options): ?RecurringScheduleTransfer
    {
        $recurringScheduleTransfer = $options[RecurringScheduleEditForm::OPTION_RECURRING_SCHEDULE] ?? null;

        if (!$recurringScheduleTransfer instanceof RecurringScheduleTransfer) {
            return null;
        }

        return $recurringScheduleTransfer;
    }
}

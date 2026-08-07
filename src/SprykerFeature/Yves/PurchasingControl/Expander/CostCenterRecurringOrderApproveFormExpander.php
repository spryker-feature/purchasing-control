<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Generated\Shared\Transfer\RecurringScheduleReviewResponseTransfer;
use Generated\Shared\Transfer\RecurringScheduleTransfer;
use SprykerFeature\Yves\OrderExperienceManagement\Form\RecurringOrderApproveForm;
use Symfony\Component\Form\FormBuilderInterface;

class CostCenterRecurringOrderApproveFormExpander implements CostCenterRecurringOrderApproveFormExpanderInterface
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
        $this->costCenterBudgetFormFieldExpander->expandForm($builder, $this->resolveSchedule($options));
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function resolveSchedule(array $options): ?RecurringScheduleTransfer
    {
        $recurringScheduleReviewResponseTransfer = $options[RecurringOrderApproveForm::OPTION_RECURRING_SCHEDULE_REVIEW] ?? null;

        if (!$recurringScheduleReviewResponseTransfer instanceof RecurringScheduleReviewResponseTransfer) {
            return null;
        }

        return $recurringScheduleReviewResponseTransfer->getRecurringSchedule();
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Plugin\OrderExperienceManagement;

use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerFeature\Yves\OrderExperienceManagement\Dependency\Plugin\RecurringOrderApproveFormExpanderPluginInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 */
class CostCenterRecurringOrderApproveFormExpanderPlugin extends AbstractPlugin implements RecurringOrderApproveFormExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Adds cost center + budget dropdowns (available-only choices) to the approve form.
     * - Field-level validation (choice membership + budget cost-center pairing) is the server-side guard.
     *
     * @api
     *
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void
    {
        $this->getFactory()->createCostCenterRecurringOrderApproveFormExpander()->expandForm($builder, $options);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Symfony\Component\Form\FormBuilderInterface;

interface CostCenterRecurringScheduleEditFormExpanderInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void;
}

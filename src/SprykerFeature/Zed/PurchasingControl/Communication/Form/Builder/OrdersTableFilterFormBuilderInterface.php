<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form\Builder;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface OrdersTableFilterFormBuilderInterface
{
    public function expandConfigureOptions(OptionsResolver $resolver): void;

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function expandOptions(array $options, Request $request): array;

    /**
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void;
}

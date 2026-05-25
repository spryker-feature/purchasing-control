<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrdersTableFilterFormExpanderPluginInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class CostCenterOrdersTableFilterFormExpanderPlugin extends AbstractPlugin implements OrdersTableFilterFormExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Declares cost center and budget options in the OptionsResolver.
     *
     * @api
     */
    public function expandConfigureOptions(OptionsResolver $resolver): void
    {
        $this->getFactory()->createOrdersTableFilterFormBuilder()->expandConfigureOptions($resolver);
    }

    /**
     * {@inheritDoc}
     * - Loads active cost center choices from the facade.
     * - Loads budget choices only for the budget IDs already selected in the request.
     *
     * @api
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function expandOptions(array $options, Request $request): array
    {
        return $this->getFactory()->createOrdersTableFilterFormBuilder()->expandOptions($options, $request);
    }

    /**
     * {@inheritDoc}
     * - Adds a cost center multi-select field to the filter form.
     * - Adds an AJAX-backed budget multi-select field that filters by the selected cost centers.
     *
     * @api
     *
     * @param array<string, mixed> $options
     */
    public function expandForm(FormBuilderInterface $builder, array $options): void
    {
        $this->getFactory()->createOrdersTableFilterFormBuilder()->expandForm($builder, $options);
    }
}

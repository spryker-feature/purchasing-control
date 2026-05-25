<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Spryker\Zed\SalesExtension\Dependency\Plugin\OrdersTableFilterFormExpanderPluginInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\Builder\OrdersTableFilterFormBuilderInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\Sales\CostCenterOrdersTableFilterFormExpanderPlugin;
use SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group CostCenterOrdersTableFilterFormExpanderPluginTest
 */
class CostCenterOrdersTableFilterFormExpanderPluginTest extends Unit
{
    public function testPluginImplementsInterface(): void
    {
        $this->assertInstanceOf(
            OrdersTableFilterFormExpanderPluginInterface::class,
            new CostCenterOrdersTableFilterFormExpanderPlugin(),
        );
    }

    public function testExpandConfigureOptionsDefinesCostCentersAndBudgetsOptions(): void
    {
        // Arrange
        $resolver = new OptionsResolver();
        $plugin = $this->createPluginWithMockedBuilder(
            expandConfigureOptions: function (OptionsResolver $resolver): void {
                $resolver->setDefined(['cost_centers', 'budgets']);
            },
        );

        // Act
        $plugin->expandConfigureOptions($resolver);

        // Assert
        $defined = $resolver->getDefinedOptions();
        $this->assertContains('cost_centers', $defined);
        $this->assertContains('budgets', $defined);
    }

    private function createPluginWithMockedBuilder(callable $expandConfigureOptions): CostCenterOrdersTableFilterFormExpanderPlugin
    {
        $formBuilderMock = $this->makeEmpty(OrdersTableFilterFormBuilderInterface::class, [
            'expandConfigureOptions' => $expandConfigureOptions,
        ]);

        $factoryMock = $this->makeEmpty(PurchasingControlCommunicationFactory::class, [
            'createOrdersTableFilterFormBuilder' => $formBuilderMock,
        ]);

        return new class ($factoryMock) extends CostCenterOrdersTableFilterFormExpanderPlugin {
            public function __construct(private PurchasingControlCommunicationFactory $mockedFactory)
            {
            }

            public function getFactory(): PurchasingControlCommunicationFactory
            {
                return $this->mockedFactory;
            }
        };
    }
}

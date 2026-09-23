<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\PurchasingControl\Plugin\OrderExperienceManagement;

use Codeception\Test\Unit;
use Generated\Api\Backend\OrdersBackendResource;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use SprykerFeature\Glue\PurchasingControl\Plugin\OrderExperienceManagement\BudgetOrderResourceExpanderPlugin;
use SprykerFeatureTest\Glue\PurchasingControl\PurchasingControlGlueTester;

/**
 * @group SprykerFeatureTest
 * @group Glue
 * @group PurchasingControl
 * @group Plugin
 * @group OrderExperienceManagement
 * @group BudgetOrderResourceExpanderPluginTest
 */
class BudgetOrderResourceExpanderPluginTest extends Unit
{
    protected const string BUDGET_UUID = '3fa85f64-5717-4562-b3fc-2c963f66afa6';

    protected const string BUDGET_NAME = 'Marketing Q2 2025';

    protected const int BUDGET_AMOUNT = 5000000;

    protected const string CURRENCY_ISO_CODE = 'EUR';

    protected const string STARTS_AT = '2025-04-01 00:00:00.000000';

    protected const string ENDS_AT = '2025-06-30 23:59:59.000000';

    protected const string ORDER_REFERENCE = 'DE--1234';

    protected PurchasingControlGlueTester $tester;

    public function testExpandReportsTheBudgetTheOrderWasChargedAgainst(): void
    {
        // Arrange
        $orderTransfer = (new OrderTransfer())->setBudget(
            (new BudgetTransfer())
                ->setUuid(static::BUDGET_UUID)
                ->setName(static::BUDGET_NAME)
                ->setAmount(static::BUDGET_AMOUNT)
                ->setCurrencyIsoCode(static::CURRENCY_ISO_CODE)
                ->setStartsAt(static::STARTS_AT)
                ->setEndsAt(static::ENDS_AT),
        );

        // Act
        $ordersBackendResource = (new BudgetOrderResourceExpanderPlugin())
            ->expand(new OrdersBackendResource(), $orderTransfer);

        // Assert
        $this->assertNotNull($ordersBackendResource->budget);
        $this->assertSame(static::BUDGET_UUID, $ordersBackendResource->budget->getUuid());
        $this->assertSame(static::BUDGET_NAME, $ordersBackendResource->budget->getName());
        $this->assertSame(static::BUDGET_AMOUNT, $ordersBackendResource->budget->getAmount());
        $this->assertSame(static::CURRENCY_ISO_CODE, $ordersBackendResource->budget->getCurrencyIsoCode());
        $this->assertSame(static::STARTS_AT, $ordersBackendResource->budget->getStartsAt());
        $this->assertSame(static::ENDS_AT, $ordersBackendResource->budget->getEndsAt());
    }

    public function testExpandLeavesBudgetNullWhenTheOrderWasChargedAgainstNone(): void
    {
        // Arrange
        $ordersBackendResource = new OrdersBackendResource();

        // Act
        $ordersBackendResource = (new BudgetOrderResourceExpanderPlugin())
            ->expand($ordersBackendResource, new OrderTransfer());

        // Assert
        $this->assertNull($ordersBackendResource->budget);
    }

    public function testExpandLeavesPropertiesOwnedByOtherModulesUntouched(): void
    {
        // Arrange
        $ordersBackendResource = new OrdersBackendResource();
        $ordersBackendResource->orderReference = static::ORDER_REFERENCE;
        $ordersBackendResource->companyBusinessUnitUuid = static::BUDGET_UUID;

        // Act
        $ordersBackendResource = (new BudgetOrderResourceExpanderPlugin())
            ->expand($ordersBackendResource, new OrderTransfer());

        // Assert
        $this->assertSame(static::ORDER_REFERENCE, $ordersBackendResource->orderReference);
        $this->assertSame(static::BUDGET_UUID, $ordersBackendResource->companyBusinessUnitUuid);
    }
}

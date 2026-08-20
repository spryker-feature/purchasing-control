<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Resolver;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Customer\CustomerClientInterface;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;
use SprykerFeature\Yves\PurchasingControl\Resolver\CostCenterResolver;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Resolver
 * @group CostCenterResolverTest
 */
class CostCenterResolverTest extends Unit
{
    protected const int ID_COMPANY_BUSINESS_UNIT = 1;

    protected const int ID_COMPANY_BUSINESS_UNIT_SESSION = 2;

    protected const int ID_COST_CENTER = 3;

    protected const string CURRENCY_CODE = 'EUR';

    /**
     * The quote request pages pass the business unit of the quote request owner, which is the only
     * way an agent or a second company user sees the cost centers the quote actually belongs to.
     */
    public function testResolveCostCentersReadsCostCentersOfTheGivenCompanyBusinessUnit(): void
    {
        // Arrange
        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock->expects($this->once())
            ->method('getActiveCostCentersForCompanyBusinessUnit')
            ->with(static::ID_COMPANY_BUSINESS_UNIT, static::CURRENCY_CODE, false)
            ->willReturn($this->createCostCenterCollection());

        $customerClientMock = $this->createCustomerClientMock(static::ID_COMPANY_BUSINESS_UNIT_SESSION);
        $customerClientMock->expects($this->never())->method('getCustomer');

        // Act
        $costCenterTransfers = (new CostCenterResolver($customerClientMock, $costCenterReaderMock))
            ->resolveCostCenters($this->createQuote(), static::ID_COMPANY_BUSINESS_UNIT);

        // Assert
        $this->assertCount(1, $costCenterTransfers);
    }

    /**
     * A caller that knows the owning business unit must not have it silently replaced by the
     * business unit of whoever is logged in.
     */
    public function testResolveCostCentersPrefersTheGivenCompanyBusinessUnitOverTheLoggedInOne(): void
    {
        // Arrange
        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock->expects($this->once())
            ->method('getActiveCostCentersForCompanyBusinessUnit')
            ->with(static::ID_COMPANY_BUSINESS_UNIT)
            ->willReturn($this->createCostCenterCollection());

        // Act
        (new CostCenterResolver(
            $this->createCustomerClientMock(static::ID_COMPANY_BUSINESS_UNIT_SESSION),
            $costCenterReaderMock,
        ))->resolveCostCenters($this->createQuote(), static::ID_COMPANY_BUSINESS_UNIT);
    }

    public function testResolveCostCentersFallsBackToTheLoggedInCompanyBusinessUnit(): void
    {
        // Arrange
        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock->expects($this->once())
            ->method('getActiveCostCentersForCompanyBusinessUnit')
            ->with(static::ID_COMPANY_BUSINESS_UNIT_SESSION)
            ->willReturn($this->createCostCenterCollection());

        // Act
        (new CostCenterResolver(
            $this->createCustomerClientMock(static::ID_COMPANY_BUSINESS_UNIT_SESSION),
            $costCenterReaderMock,
        ))->resolveCostCenters($this->createQuote());
    }

    public function testResolveCostCentersReturnsNothingWhenNoBusinessUnitCanBeResolved(): void
    {
        // Arrange
        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock->expects($this->never())->method('getActiveCostCentersForCompanyBusinessUnit');

        // Act
        $costCenterTransfers = (new CostCenterResolver($this->createCustomerClientMock(), $costCenterReaderMock))
            ->resolveCostCenters($this->createQuote());

        // Assert
        $this->assertCount(0, $costCenterTransfers);
    }

    public function testResolveCostCentersReturnsNothingWhenCustomerIsNotACompanyUser(): void
    {
        // Arrange
        $customerClientMock = $this->createCustomerClientMock();
        $customerClientMock->method('getCustomer')->willReturn(new CustomerTransfer());

        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock->expects($this->never())->method('getActiveCostCentersForCompanyBusinessUnit');

        // Act
        $costCenterTransfers = (new CostCenterResolver($customerClientMock, $costCenterReaderMock))
            ->resolveCostCenters($this->createQuote());

        // Assert
        $this->assertCount(0, $costCenterTransfers);
    }

    /**
     * @return \Spryker\Client\Customer\CustomerClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createCustomerClientMock(?int $idCompanyBusinessUnit = null): CustomerClientInterface
    {
        $customerClientMock = $this->createMock(CustomerClientInterface::class);

        if ($idCompanyBusinessUnit === null) {
            return $customerClientMock;
        }

        $customerTransfer = (new CustomerTransfer())->setCompanyUserTransfer(
            (new CompanyUserTransfer())->setFkCompanyBusinessUnit($idCompanyBusinessUnit),
        );
        $customerClientMock->method('getCustomer')->willReturn($customerTransfer);

        return $customerClientMock;
    }

    protected function createCostCenterCollection(): CostCenterCollectionTransfer
    {
        return (new CostCenterCollectionTransfer())->addCostCenter(
            (new CostCenterTransfer())->setIdCostCenter(static::ID_COST_CENTER),
        );
    }

    protected function createQuote(): QuoteTransfer
    {
        return (new QuoteTransfer())->setCurrency((new CurrencyTransfer())->setCode(static::CURRENCY_CODE));
    }
}

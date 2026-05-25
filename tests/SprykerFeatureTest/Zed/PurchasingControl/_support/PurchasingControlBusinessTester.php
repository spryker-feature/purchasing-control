<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\DataBuilder\BudgetBuilder;
use Generated\Shared\DataBuilder\CostCenterBuilder;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemStateQuery;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcess;
use Orm\Zed\Oms\Persistence\SpyOmsOrderProcessQuery;
use Orm\Zed\Sales\Persistence\SpySalesExpense;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Orm\Zed\Sales\Persistence\SpySalesShipment;
use ReflectionProperty;
use Spryker\Shared\Kernel\BundleConfigMock\BundleConfigMock;
use SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class PurchasingControlBusinessTester extends Actor
{
    use _generated\PurchasingControlBusinessTesterActions;

    public const string FK_BUDGET = 'fkBudget';

    public const string FK_COST_CENTER = 'fkCostCenter';

    public const string ID_COMPANY_BUSINESS_UNIT = 'idCompanyBusinessUnit';

    public const string STARTS_AT = 'startsAt';

    public const string ENDS_AT = 'endsAt';

    public const string STATE_NAME = 'stateName';

    public const string PROCESS_NAME = 'processName';

    public const string GROSS_PRICE = 'grossPrice';

    public const string PRICE_TO_PAY_AGGREGATION = 'priceToPayAggregation';

    public const string REFUNDABLE_AMOUNT = 'refundableAmount';

    public const string ITEM_NAME = 'name';

    public const string SKU = 'sku';

    public const string FK_SALES_SHIPMENT = 'fkSalesShipment';

    public const string EXPENSE_TYPE = 'type';

    protected const string DEFAULT_OMS_STATE = 'new';

    protected const string DEFAULT_OMS_PROCESS = 'test-process';

    protected const string DEFAULT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    protected const string DEFAULT_ITEM_NAME = 'Test Item';

    protected const string DEFAULT_STARTS_AT = '-1 day';

    protected const string DEFAULT_ENDS_AT = '+30 days';

    protected const string BILLING_ADDRESS_COUNTRY = 'DE';

    public function getFacade(): PurchasingControlFacadeInterface
    {
        return $this->getLocator()->purchasingControl()->facade();
    }

    public function buildCostCenterTransfer(array $overrides = []): CostCenterTransfer
    {
        $companyTransfer = $this->haveCompany();
        $idCompanyBusinessUnit = $overrides[static::ID_COMPANY_BUSINESS_UNIT]
            ?? $this->haveCompanyBusinessUnit([
                CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
                CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
            ])->getIdCompanyBusinessUnitOrFail();

        unset($overrides[static::ID_COMPANY_BUSINESS_UNIT]);

        return (new CostCenterBuilder($overrides))->build()
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit);
    }

    public function buildBudgetTransfer(int $idCostCenter, array $overrides = []): BudgetTransfer
    {
        $defaults = [
            static::STARTS_AT => date('Y-m-d', strtotime(static::DEFAULT_STARTS_AT)),
            static::ENDS_AT => date('Y-m-d', strtotime(static::DEFAULT_ENDS_AT)),
        ];

        return (new BudgetBuilder(array_merge($defaults, $overrides)))->build()
            ->setIdCostCenter($idCostCenter);
    }

    public function haveSalesOrderId(): int
    {
        return $this->haveSalesOrderEntity()->getIdSalesOrder();
    }

    public function haveSalesOrderEntity(array $overrides = []): SpySalesOrder
    {
        $countryId = (int)SpyCountryQuery::create()
            ->filterByIso2Code(static::BILLING_ADDRESS_COUNTRY)
            ->findOne()
            ->getIdCountry();

        $address = new SpySalesOrderAddress();
        $address->setFirstName('Test');
        $address->setLastName('Test');
        $address->setCity('City');
        $address->setZipCode('12345');
        $address->setFkCountry($countryId);
        $address->save();

        $order = new SpySalesOrder();
        $order->setOrderReference('TEST-' . uniqid());
        $order->setFkSalesOrderAddressBilling($address->getIdSalesOrderAddress());

        if (isset($overrides[static::FK_BUDGET])) {
            $order->setFkBudget($overrides[static::FK_BUDGET]);
        }

        if (isset($overrides[static::FK_COST_CENTER])) {
            $order->setFkCostCenter($overrides[static::FK_COST_CENTER]);
        }

        $order->save();

        return $order;
    }

    public function haveSalesOrderItem(int $idSalesOrder, array $overrides = []): SpySalesOrderItem
    {
        $omsState = $this->haveOmsOrderItemState($overrides[static::STATE_NAME] ?? static::DEFAULT_OMS_STATE);
        $omsProcess = $this->haveOmsOrderProcess($overrides[static::PROCESS_NAME] ?? static::DEFAULT_OMS_PROCESS);

        $item = new SpySalesOrderItem();
        $item->setFkSalesOrder($idSalesOrder);
        $item->setFkOmsOrderItemState($omsState->getIdOmsOrderItemState());
        $item->setFkOmsOrderProcess($omsProcess->getIdOmsOrderProcess());
        $item->setGrossPrice($overrides[static::GROSS_PRICE] ?? 0);
        $item->setPriceToPayAggregation($overrides[static::PRICE_TO_PAY_AGGREGATION] ?? 0);
        $item->setRefundableAmount($overrides[static::REFUNDABLE_AMOUNT] ?? 0);
        $item->setName($overrides[static::ITEM_NAME] ?? static::DEFAULT_ITEM_NAME);
        $item->setSku($overrides[static::SKU] ?? uniqid('SKU-'));

        if (isset($overrides[static::FK_SALES_SHIPMENT])) {
            $item->setFkSalesShipment($overrides[static::FK_SALES_SHIPMENT]);
        }

        $item->save();

        return $item;
    }

    public function haveSalesExpense(int $idSalesOrder, array $overrides = []): SpySalesExpense
    {
        $grossPrice = $overrides[static::GROSS_PRICE] ?? 0;

        $expense = new SpySalesExpense();
        $expense->setFkSalesOrder($idSalesOrder);
        $expense->setType($overrides[static::EXPENSE_TYPE] ?? static::DEFAULT_EXPENSE_TYPE);
        $expense->setGrossPrice($grossPrice);
        $expense->setRefundableAmount($overrides[static::REFUNDABLE_AMOUNT] ?? $grossPrice);
        $expense->save();

        return $expense;
    }

    public function haveSalesShipment(int $idSalesOrder, int $idSalesExpense): SpySalesShipment
    {
        $shipment = new SpySalesShipment();
        $shipment->setFkSalesOrder($idSalesOrder);
        $shipment->setFkSalesExpense($idSalesExpense);
        $shipment->save();

        return $shipment;
    }

    public function haveOmsOrderItemState(string $name): SpyOmsOrderItemState
    {
        $existing = SpyOmsOrderItemStateQuery::create()->filterByName($name)->findOne();
        if ($existing !== null) {
            return $existing;
        }

        $state = new SpyOmsOrderItemState();
        $state->setName($name);
        $state->save();

        return $state;
    }

    public function haveOmsOrderProcess(string $name): SpyOmsOrderProcess
    {
        $existing = SpyOmsOrderProcessQuery::create()->filterByName($name)->findOne();
        if ($existing !== null) {
            return $existing;
        }

        $process = new SpyOmsOrderProcess();
        $process->setName($name);
        $process->save();

        return $process;
    }

    public function mockPurchasingControlConfig(string $methodName, mixed $return): void
    {
        /** @var \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig $configStub */
        $configStub = Stub::make(PurchasingControlConfig::class, [$methodName => $return]);

        $property = new ReflectionProperty(BundleConfigMock::class, 'bundleConfigMocks');
        $property->setAccessible(true);
        $mocks = $property->getValue(null) ?? [];
        $mocks[PurchasingControlConfig::class] = $configStub;
        $property->setValue(null, $mocks);
    }
}

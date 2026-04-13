<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\PurchasingControl;

use Codeception\Actor;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyBudget;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenter;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterToCompanyBusinessUnit;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface;

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

    public function getFacade(): PurchasingControlFacadeInterface
    {
        return $this->getLocator()->purchasingControl()->facade();
    }

    public function buildCostCenterTransfer(array $overrides = []): CostCenterTransfer
    {
        $idCompanyBusinessUnit = $overrides['idCompanyBusinessUnit']
            ?? $this->haveCompanyBusinessUnit()->getIdCompanyBusinessUnitOrFail();

        return (new CostCenterTransfer())
            ->setName($overrides['name'] ?? 'Test Cost Center')
            ->addIdCompanyBusinessUnit($idCompanyBusinessUnit)
            ->setIsActive($overrides['isActive'] ?? true);
    }

    public function haveCostCenter(array $overrides = []): CostCenterTransfer
    {
        $costCenterTransfer = $this->buildCostCenterTransfer($overrides);

        $entity = new SpyCostCenter();
        $entity->setName($costCenterTransfer->getNameOrFail());
        $entity->setIsActive((bool)$costCenterTransfer->getIsActive());
        $entity->save();

        $idCostCenter = $entity->getIdCostCenter();

        foreach ($costCenterTransfer->getCompanyBusinessUnitIds() as $idCompanyBusinessUnit) {
            $junction = new SpyCostCenterToCompanyBusinessUnit();
            $junction->setFkCostCenter($idCostCenter);
            $junction->setFkCompanyBusinessUnit($idCompanyBusinessUnit);
            $junction->save();
        }

        $costCenterTransfer->setIdCostCenter($idCostCenter);

        return $costCenterTransfer;
    }

    public function buildBudgetTransfer(int $idCostCenter, array $overrides = []): BudgetTransfer
    {
        return (new BudgetTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setName($overrides['name'] ?? 'Test Budget')
            ->setAmount($overrides['amount'] ?? 10000)
            ->setCurrencyIsoCode($overrides['currencyIsoCode'] ?? 'EUR')
            ->setStartsAt($overrides['startsAt'] ?? date('Y-m-d', strtotime('-10 days')))
            ->setEndsAt($overrides['endsAt'] ?? date('Y-m-d', strtotime('+10 days')))
            ->setEnforcementRule($overrides['enforcementRule'] ?? 'block')
            ->setIsActive($overrides['isActive'] ?? true);
    }

    public function haveBudget(int $idCostCenter, array $overrides = []): BudgetTransfer
    {
        $budgetTransfer = $this->buildBudgetTransfer($idCostCenter, $overrides);

        $entity = new SpyBudget();
        $entity->fromArray($budgetTransfer->modifiedToArray());
        $entity->setFkCostCenter($budgetTransfer->getIdCostCenterOrFail());
        $entity->save();

        $budgetTransfer->setIdBudget($entity->getIdBudget());

        return $budgetTransfer;
    }

    public function haveSalesOrderId(): int
    {
        $countryId = (int)SpyCountryQuery::create()
            ->filterByIso2Code('DE')
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
        $order->save();

        return $order->getIdSalesOrder();
    }

    public function haveCompanyBusinessUnit(): CompanyBusinessUnitTransfer
    {
        $companyTransfer = $this->getLocator()->company()->facade()->create(
            (new CompanyTransfer())->setName('Test Company ' . uniqid())->setIsActive(true),
        )->getCompanyTransfer();

        $companyBusinessUnitTransfer = $this->getLocator()->companyBusinessUnit()->facade()->create(
            (new CompanyBusinessUnitTransfer())
                ->setName('Test BU ' . uniqid())
                ->setFkCompany($companyTransfer->getIdCompanyOrFail()),
        )->getCompanyBusinessUnitTransfer();

        return $companyBusinessUnitTransfer;
    }
}

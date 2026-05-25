<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetCollectionRequestTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig as SharedPurchasingControlConfig;
use SprykerFeatureTest\Zed\PurchasingControl\PurchasingControlBusinessTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group CostCenter
 * @group Business
 * @group PurchasingControlFacadeTest
 */
class PurchasingControlFacadeTest extends Unit
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_AMOUNT_INVALID
     */
    protected const string GLOSSARY_KEY_BUDGET_AMOUNT_INVALID = 'purchasing_control.budget.validation.amount_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_CURRENCY_INVALID
     */
    protected const string GLOSSARY_KEY_BUDGET_CURRENCY_INVALID = 'purchasing_control.budget.validation.currency_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\Budget\BudgetValidator::GLOSSARY_KEY_VALIDATION_DATE_RANGE_INVALID
     */
    protected const string GLOSSARY_KEY_BUDGET_DATE_RANGE_INVALID = 'purchasing_control.budget.validation.date_range_invalid';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_VALIDATION_NAME_EMPTY
     */
    protected const string GLOSSARY_KEY_COST_CENTER_NAME_EMPTY = 'purchasing_control.cost_center.validation.name_empty';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterValidator::GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY
     */
    protected const string GLOSSARY_KEY_COST_CENTER_BUSINESS_UNIT_EMPTY = 'purchasing_control.cost_center.validation.business_unit_empty';

    protected const string COMPANY_BUSINESS_UNIT = 'companyBusinessUnit';

    protected PurchasingControlBusinessTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensurePurchasingControlTablesAreEmpty();
    }

    public function testCreateCostCenterCollectionPersistsCostCenter(): void
    {
        $costCenterTransfer = $this->tester->buildCostCenterTransfer();

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCostCenter($costCenterTransfer),
        );

        $this->assertEmpty($response->getErrors());
        $savedTransfer = $response->getCostCenters()->getIterator()->current();
        $this->assertNotNull($savedTransfer->getIdCostCenter());
        $this->assertSame($costCenterTransfer->getName(), $savedTransfer->getName());
    }

    public function testCreateCostCenterCollectionReturnsErrorWhenNameIsEmpty(): void
    {
        $costCenterTransfer = $this->tester->buildCostCenterTransfer(['name' => '']);

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCostCenter($costCenterTransfer),
        );

        $this->assertNotEmpty($response->getErrors());
    }

    public function testUpdateCostCenterCollectionChangesName(): void
    {
        $costCenterTransfer = $this->tester->haveCostCenter();
        $costCenterTransfer->setName('Updated Name');

        $response = $this->tester->getFacade()->updateCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCostCenter($costCenterTransfer),
        );

        $this->assertEmpty($response->getErrors());
        $this->assertSame('Updated Name', $response->getCostCenters()->getIterator()->current()->getName());
    }

    public function testGetCostCenterCollectionFiltersByBusinessUnit(): void
    {
        $companyTransfer = $this->tester->haveCompany();
        $companyBusinessUnitTransfer = $this->tester->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
            CompanyBusinessUnitTransfer::COMPANY => $companyTransfer,
        ]);

        $this->tester->haveCostCenter([static::COMPANY_BUSINESS_UNIT => $companyBusinessUnitTransfer]);
        $this->tester->haveCostCenter();

        $collection = $this->tester->getFacade()->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->addIdCompanyBusinessUnit(
                    $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
                ),
            ),
        );

        $this->assertCount(1, $collection->getCostCenters());
        $this->assertContains(
            $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            $collection->getCostCenters()->offsetGet(0)->getCompanyBusinessUnitIds(),
        );
    }

    public function testGetCostCenterCollectionFiltersByIsActive(): void
    {
        $this->tester->haveCostCenter(['isActive' => true]);
        $this->tester->haveCostCenter(['isActive' => false]);

        $collection = $this->tester->getFacade()->getCostCenterCollection(
            (new CostCenterCriteriaTransfer())->setCostCenterConditions(
                (new CostCenterConditionsTransfer())->setIsActive(true),
            ),
        );

        foreach ($collection->getCostCenters() as $costCenter) {
            $this->assertTrue($costCenter->getIsActive());
        }
    }

    public function testCreateBudgetCollectionPersistsBudget(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->buildBudgetTransfer($costCenter->getIdCostCenterOrFail());

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addBudget($budgetTransfer),
        );

        $this->assertEmpty($response->getErrors());
        $savedBudget = $response->getBudgets()->getIterator()->current();
        $this->assertNotNull($savedBudget->getIdBudget());
        $this->assertSame($budgetTransfer->getName(), $savedBudget->getName());
    }

    public function testUpdateBudgetCollectionChangesEnforcementRule(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $budgetTransfer = $this->tester->haveBudget($costCenter->getIdCostCenterOrFail());
        $budgetTransfer->setEnforcementRule(SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN);

        $response = $this->tester->getFacade()->updateBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addBudget($budgetTransfer),
        );

        $this->assertEmpty($response->getErrors());
        $this->assertSame(
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN,
            $response->getBudgets()->getIterator()->current()->getEnforcementRule(),
        );
    }

    public function testCreateCostCenterCollectionTransactionalModeSkipsAllWhenAnyItemIsInvalid(): void
    {
        $validTransfer = $this->tester->buildCostCenterTransfer();
        $invalidTransfer = $this->tester->buildCostCenterTransfer(['name' => '']);

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCostCenter($validTransfer)
                ->addCostCenter($invalidTransfer),
        );

        $this->assertCount(1, $response->getErrors());
        $this->assertCount(2, $response->getCostCenters());
        $this->assertSame(0, SpyCostCenterQuery::create()->count());
    }

    public function testCreateCostCenterCollectionNonTransactionalModePersistsValidItems(): void
    {
        $validTransfer = $this->tester->buildCostCenterTransfer();
        $invalidTransfer = $this->tester->buildCostCenterTransfer(['name' => '']);

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(false)
                ->addCostCenter($validTransfer)
                ->addCostCenter($invalidTransfer),
        );

        $this->assertCount(1, $response->getErrors());
        $this->assertCount(2, $response->getCostCenters());
        $this->assertSame(1, SpyCostCenterQuery::create()->count());

        $persistedTransfers = array_values(iterator_to_array($response->getCostCenters()));
        $this->assertNotNull($persistedTransfers[0]->getIdCostCenter());
        $this->assertNull($persistedTransfers[1]->getIdCostCenter());
    }

    public function testUpdateCostCenterCollectionTransactionalModeSkipsAllWhenAnyItemIsInvalid(): void
    {
        $firstTransfer = $this->tester->haveCostCenter(['name' => 'Original A']);
        $secondTransfer = $this->tester->haveCostCenter(['name' => 'Original B']);
        $secondTransfer->setName('');

        $this->tester->getFacade()->updateCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addCostCenter($firstTransfer->setName('Changed A'))
                ->addCostCenter($secondTransfer),
        );

        $this->assertSame(
            'Original A',
            SpyCostCenterQuery::create()->findPk($firstTransfer->getIdCostCenterOrFail())->getName(),
        );
    }

    public function testUpdateCostCenterCollectionNonTransactionalModePersistsValidItems(): void
    {
        $firstTransfer = $this->tester->haveCostCenter(['name' => 'Original A']);
        $secondTransfer = $this->tester->haveCostCenter(['name' => 'Original B']);

        $response = $this->tester->getFacade()->updateCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())
                ->setIsTransactional(false)
                ->addCostCenter($firstTransfer->setName('Updated A'))
                ->addCostCenter($secondTransfer->setName('')),
        );

        $this->assertCount(1, $response->getErrors());
        $this->assertSame(
            'Updated A',
            SpyCostCenterQuery::create()->findPk($firstTransfer->getIdCostCenterOrFail())->getName(),
        );
        $this->assertSame(
            'Original B',
            SpyCostCenterQuery::create()->findPk($secondTransfer->getIdCostCenterOrFail())->getName(),
        );
    }

    public function testCreateBudgetCollectionTransactionalModeSkipsAllWhenAnyItemIsInvalid(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $idCostCenter = $costCenter->getIdCostCenterOrFail();

        $validBudget = $this->tester->buildBudgetTransfer($idCostCenter);
        $invalidBudget = $this->tester->buildBudgetTransfer($idCostCenter, ['amount' => 0]);

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addBudget($validBudget)
                ->addBudget($invalidBudget),
        );

        $this->assertNotEmpty($response->getErrors());
        $this->assertCount(2, $response->getBudgets());
        $this->assertSame(0, SpyBudgetQuery::create()->count());
    }

    public function testCreateBudgetCollectionNonTransactionalModePersistsValidBudgets(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $idCostCenter = $costCenter->getIdCostCenterOrFail();

        $validBudget = $this->tester->buildBudgetTransfer($idCostCenter);
        $invalidBudget = $this->tester->buildBudgetTransfer($idCostCenter, ['amount' => 0]);

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(false)
                ->addBudget($validBudget)
                ->addBudget($invalidBudget),
        );

        $this->assertNotEmpty($response->getErrors());
        $this->assertCount(2, $response->getBudgets());
        $this->assertSame(1, SpyBudgetQuery::create()->count());

        $persistedBudgets = array_values(iterator_to_array($response->getBudgets()));
        $this->assertNotNull($persistedBudgets[0]->getIdBudget());
        $this->assertNull($persistedBudgets[1]->getIdBudget());
    }

    public function testUpdateBudgetCollectionTransactionalModeSkipsAllWhenAnyItemIsInvalid(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $idCostCenter = $costCenter->getIdCostCenterOrFail();

        $firstBudget = $this->tester->haveBudget($idCostCenter, ['amount' => 1000]);
        $secondBudget = $this->tester->haveBudget($idCostCenter, ['amount' => 2000]);

        $this->tester->getFacade()->updateBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(true)
                ->addBudget($firstBudget->setAmount(9999))
                ->addBudget($secondBudget->setAmount(0)),
        );

        $this->assertSame(
            1000,
            (int)SpyBudgetQuery::create()->findPk($firstBudget->getIdBudgetOrFail())->getAmount(),
        );
    }

    public function testUpdateBudgetCollectionNonTransactionalModePersistsValidBudgets(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $idCostCenter = $costCenter->getIdCostCenterOrFail();

        $firstBudget = $this->tester->haveBudget($idCostCenter, ['amount' => 1000]);
        $secondBudget = $this->tester->haveBudget($idCostCenter, ['amount' => 2000]);

        $response = $this->tester->getFacade()->updateBudgetCollection(
            (new BudgetCollectionRequestTransfer())
                ->setIsTransactional(false)
                ->addBudget($firstBudget->setAmount(9999))
                ->addBudget($secondBudget->setAmount(0)),
        );

        $this->assertNotEmpty($response->getErrors());
        $this->assertSame(
            9999,
            (int)SpyBudgetQuery::create()->findPk($firstBudget->getIdBudgetOrFail())->getAmount(),
        );
        $this->assertSame(
            2000,
            (int)SpyBudgetQuery::create()->findPk($secondBudget->getIdBudgetOrFail())->getAmount(),
        );
    }

    public function testCreateBudgetCollectionReturnsAmountInvalidGlossaryKey(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $invalidBudget = $this->tester->buildBudgetTransfer($costCenter->getIdCostCenterOrFail(), ['amount' => 0]);

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())->setIsTransactional(true)->addBudget($invalidBudget),
        );

        $this->assertSame(
            static::GLOSSARY_KEY_BUDGET_AMOUNT_INVALID,
            $response->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testCreateBudgetCollectionReturnsCurrencyInvalidGlossaryKey(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $invalidBudget = $this->tester->buildBudgetTransfer($costCenter->getIdCostCenterOrFail(), ['currencyIsoCode' => 'EU']);

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())->setIsTransactional(true)->addBudget($invalidBudget),
        );

        $this->assertSame(
            static::GLOSSARY_KEY_BUDGET_CURRENCY_INVALID,
            $response->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testCreateBudgetCollectionReturnsDateRangeInvalidGlossaryKey(): void
    {
        $costCenter = $this->tester->haveCostCenter();
        $invalidBudget = $this->tester->buildBudgetTransfer($costCenter->getIdCostCenterOrFail(), [
            'startsAt' => date('Y-m-d', strtotime('+10 days')),
            'endsAt' => date('Y-m-d', strtotime('-10 days')),
        ]);

        $response = $this->tester->getFacade()->createBudgetCollection(
            (new BudgetCollectionRequestTransfer())->setIsTransactional(true)->addBudget($invalidBudget),
        );

        $this->assertSame(
            static::GLOSSARY_KEY_BUDGET_DATE_RANGE_INVALID,
            $response->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testCreateCostCenterCollectionReturnsNameEmptyGlossaryKey(): void
    {
        $costCenterTransfer = $this->tester->buildCostCenterTransfer(['name' => '']);

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())->setIsTransactional(true)->addCostCenter($costCenterTransfer),
        );

        $this->assertSame(
            static::GLOSSARY_KEY_COST_CENTER_NAME_EMPTY,
            $response->getErrors()->getIterator()->current()->getMessage(),
        );
    }

    public function testCreateCostCenterCollectionReturnsBusinessUnitEmptyGlossaryKey(): void
    {
        $costCenterTransfer = (new CostCenterTransfer())
            ->setName('Valid Name')
            ->setIsActive(true);

        $response = $this->tester->getFacade()->createCostCenterCollection(
            (new CostCenterCollectionRequestTransfer())->setIsTransactional(true)->addCostCenter($costCenterTransfer),
        );

        $this->assertSame(
            static::GLOSSARY_KEY_COST_CENTER_BUSINESS_UNIT_EMPTY,
            $response->getErrors()->getIterator()->current()->getMessage(),
        );
    }
}

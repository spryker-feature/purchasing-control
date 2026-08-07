<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Expander;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\BudgetSummaryTransfer;
use Generated\Shared\Transfer\BudgetTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RecurringScheduleTransfer;
use Spryker\Client\Currency\CurrencyClientInterface;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig as SharedPurchasingControlConfig;
use SprykerFeature\Yves\PurchasingControl\Expander\CostCenterBudgetFormFieldExpander;
use SprykerFeature\Yves\PurchasingControl\Formatter\BudgetSummaryFormatterInterface;
use SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Expander
 * @group CostCenterBudgetFormFieldExpanderTest
 */
class CostCenterBudgetFormFieldExpanderTest extends Unit
{
    protected const string FIELD_ID_COST_CENTER = 'idCostCenter';

    protected const string FIELD_ID_BUDGET = 'idBudget';

    protected const string GLOSSARY_KEY_INACTIVE_COST_CENTER = 'purchasing_control.validation.inactive-cost-center';

    protected const string GLOSSARY_KEY_INACTIVE_BUDGET = 'purchasing_control.validation.inactive-budget';

    protected const int ID_COMPANY_BUSINESS_UNIT = 7;

    protected const int ID_COST_CENTER = 1;

    protected const int ID_BUDGET = 2;

    protected const int ID_COST_CENTER_OTHER = 3;

    protected const int ID_BUDGET_OTHER = 4;

    protected const string CURRENCY_ISO_CODE = 'EUR';

    public function testSubmitWithDeactivatedCostCenterRejectsChoiceInsteadOfReportingExtraFields(): void
    {
        // Arrange
        $form = $this->createForm(new CostCenterCollectionTransfer());

        // Act
        $form->submit([
            static::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            static::FIELD_ID_BUDGET => (string)static::ID_BUDGET,
        ]);

        // Assert
        $this->assertSame([], $form->getExtraData());
        $this->assertCount(0, $form->getErrors());
        $this->assertFalse($form->isValid());
        $this->assertTrue($form->has(static::FIELD_ID_COST_CENTER));
        $this->assertTrue($form->has(static::FIELD_ID_BUDGET));
        $this->assertSame(static::GLOSSARY_KEY_INACTIVE_COST_CENTER, $this->getFirstErrorMessage($form, static::FIELD_ID_COST_CENTER));
        $this->assertSame(static::GLOSSARY_KEY_INACTIVE_BUDGET, $this->getFirstErrorMessage($form, static::FIELD_ID_BUDGET));
    }

    public function testSubmitWithDeactivatedBudgetRejectsBudgetOnly(): void
    {
        // Arrange
        $form = $this->createForm($this->createCostCenterCollection(withBudget: false));

        // Act
        $form->submit([
            static::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            static::FIELD_ID_BUDGET => (string)static::ID_BUDGET,
        ]);

        // Assert
        $this->assertSame([], $form->getExtraData());
        $this->assertFalse($form->isValid());
        $this->assertCount(0, $form->get(static::FIELD_ID_COST_CENTER)->getErrors());
        $this->assertSame(static::ID_COST_CENTER, $form->get(static::FIELD_ID_COST_CENTER)->getData());
        $this->assertSame(static::GLOSSARY_KEY_INACTIVE_BUDGET, $this->getFirstErrorMessage($form, static::FIELD_ID_BUDGET));
    }

    public function testSubmitWithActiveCostCenterAndBudgetKeepsFormValid(): void
    {
        // Arrange
        $form = $this->createForm($this->createCostCenterCollection());

        // Act
        $form->submit([
            static::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            static::FIELD_ID_BUDGET => (string)static::ID_BUDGET,
        ]);

        // Assert
        $this->assertTrue($form->isValid());
        $this->assertSame(static::ID_COST_CENTER, $form->get(static::FIELD_ID_COST_CENTER)->getData());
        $this->assertSame(static::ID_BUDGET, $form->get(static::FIELD_ID_BUDGET)->getData());
    }

    public function testSubmitWithStaleSelectionRejectsChoiceWhenOtherCostCentersRemainActive(): void
    {
        // Arrange
        $form = $this->createForm($this->createCostCenterCollection(
            idCostCenter: static::ID_COST_CENTER_OTHER,
            idBudget: static::ID_BUDGET_OTHER,
        ));

        // Act
        $form->submit([
            static::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            static::FIELD_ID_BUDGET => (string)static::ID_BUDGET,
        ]);

        // Assert
        $this->assertSame([], $form->getExtraData());
        $this->assertFalse($form->isValid());
        $this->assertSame(static::GLOSSARY_KEY_INACTIVE_COST_CENTER, $this->getFirstErrorMessage($form, static::FIELD_ID_COST_CENTER));
        $this->assertSame(static::GLOSSARY_KEY_INACTIVE_BUDGET, $this->getFirstErrorMessage($form, static::FIELD_ID_BUDGET));
    }

    public function testExpandFormAddsNoFieldsWhenNoActiveCostCenterExists(): void
    {
        // Arrange
        $form = $this->createForm(new CostCenterCollectionTransfer());

        // Act
        $isCostCenterFieldAdded = $form->has(static::FIELD_ID_COST_CENTER);

        // Assert: the templates render the block only when both fields exist.
        $this->assertFalse($isCostCenterFieldAdded);
        $this->assertFalse($form->has(static::FIELD_ID_BUDGET));
    }

    public function testSubmitWithoutCostCenterKeysAddsNoFields(): void
    {
        // Arrange
        $form = $this->createForm(new CostCenterCollectionTransfer());

        // Act
        $form->submit([]);

        // Assert
        $this->assertSame([], $form->getExtraData());
        $this->assertTrue($form->isValid());
        $this->assertFalse($form->has(static::FIELD_ID_COST_CENTER));
        $this->assertFalse($form->has(static::FIELD_ID_BUDGET));
    }

    /**
     * Everything was already deactivated when the page was rendered, so neither field is offered and the payload
     * carries no keys: the stored selection must still be cleared instead of reaching the next order.
     */
    public function testSubmitClearsStoredSelectionWhenNoFieldIsOffered(): void
    {
        // Arrange
        $form = $this->createForm(new CostCenterCollectionTransfer(), $this->createRecurringSchedule());

        // Act
        $form->submit([]);

        // Assert
        $formData = $form->getData();

        $this->assertTrue($form->isValid());
        $this->assertArrayHasKey(static::FIELD_ID_COST_CENTER, $formData);
        $this->assertArrayHasKey(static::FIELD_ID_BUDGET, $formData);
        $this->assertNull($formData[static::FIELD_ID_COST_CENTER]);
        $this->assertNull($formData[static::FIELD_ID_BUDGET]);
    }

    public function testExpandFormPreselectsStoredSelectionWhenFieldsAreOffered(): void
    {
        // Arrange
        $form = $this->createForm($this->createCostCenterCollection(), $this->createRecurringSchedule());

        // Act
        $idCostCenter = $form->get(static::FIELD_ID_COST_CENTER)->getData();

        // Assert
        $this->assertSame(static::ID_COST_CENTER, $idCostCenter);
        $this->assertSame(static::ID_BUDGET, $form->get(static::FIELD_ID_BUDGET)->getData());
    }

    protected function getFirstErrorMessage(FormInterface $form, string $fieldName): ?string
    {
        foreach ($form->get($fieldName)->getErrors() as $formError) {
            return $formError->getMessage();
        }

        return null;
    }

    protected function createForm(
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
        ?RecurringScheduleTransfer $recurringScheduleTransfer = null,
    ): FormInterface {
        $formBuilder = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
            ->createBuilder(FormType::class, []);

        $this->createExpander($costCenterCollectionTransfer)->expandForm($formBuilder, $recurringScheduleTransfer);

        return $formBuilder->getForm();
    }

    protected function createRecurringSchedule(): RecurringScheduleTransfer
    {
        return (new RecurringScheduleTransfer())
            ->setCurrencyIsoCode(static::CURRENCY_ISO_CODE)
            ->setQuoteData((string)json_encode([
                QuoteTransfer::ID_COST_CENTER => static::ID_COST_CENTER,
                QuoteTransfer::ID_BUDGET => static::ID_BUDGET,
            ]));
    }

    protected function createExpander(CostCenterCollectionTransfer $costCenterCollectionTransfer): CostCenterBudgetFormFieldExpander
    {
        $customerTransfer = (new CustomerTransfer())->setCompanyUserTransfer(
            (new CompanyUserTransfer())->setFkCompanyBusinessUnit(static::ID_COMPANY_BUSINESS_UNIT),
        );
        $customerClientMock = $this->createMock(CustomerClientInterface::class);
        $customerClientMock->method('getCustomer')->willReturn($customerTransfer);

        $costCenterReaderMock = $this->createMock(CostCenterReaderInterface::class);
        $costCenterReaderMock
            ->method('getActiveCostCentersForCompanyBusinessUnit')
            ->willReturn($costCenterCollectionTransfer);

        $budgetSummaryFormatterMock = $this->createMock(BudgetSummaryFormatterInterface::class);
        $budgetSummaryFormatterMock->method('formatBudgetSummary')->willReturn(new BudgetSummaryTransfer());

        $currencyClientMock = $this->createMock(CurrencyClientInterface::class);
        $currencyClientMock->method('getCurrent')->willReturn(
            (new CurrencyTransfer())->setCode(static::CURRENCY_ISO_CODE),
        );

        $configMock = $this->createMock(PurchasingControlConfig::class);
        $configMock->method('getRecurringOrderSelectableBudgetEnforcementRules')->willReturn([
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK,
            SharedPurchasingControlConfig::ENFORCEMENT_RULE_WARN,
        ]);

        $utilEncodingServiceMock = $this->createMock(UtilEncodingServiceInterface::class);
        $utilEncodingServiceMock->method('decodeJson')->willReturnCallback(
            static function (string $jsonValue, bool $assoc = false): mixed {
                return json_decode($jsonValue, $assoc);
            },
        );

        return new CostCenterBudgetFormFieldExpander(
            $customerClientMock,
            $costCenterReaderMock,
            $budgetSummaryFormatterMock,
            $utilEncodingServiceMock,
            $currencyClientMock,
            $configMock,
        );
    }

    protected function createCostCenterCollection(
        bool $withBudget = true,
        int $idCostCenter = self::ID_COST_CENTER,
        int $idBudget = self::ID_BUDGET,
    ): CostCenterCollectionTransfer {
        $costCenterTransfer = (new CostCenterTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setName('IT & Operations')
            ->setIsActive(true);

        if ($withBudget) {
            $costCenterTransfer->addBudget(
                (new BudgetTransfer())
                    ->setIdBudget($idBudget)
                    ->setIdCostCenter($idCostCenter)
                    ->setName('IT Software Licenses 2025')
                    ->setCurrencyIsoCode(static::CURRENCY_ISO_CODE)
                    ->setEnforcementRule(SharedPurchasingControlConfig::ENFORCEMENT_RULE_BLOCK)
                    ->setIsActive(true),
            );
        }

        return (new CostCenterCollectionTransfer())->addCostCenter($costCenterTransfer);
    }
}

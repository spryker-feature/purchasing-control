<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Form;

use Codeception\Test\Unit;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterSelectorForm;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Form
 * @group CostCenterSelectorFormTest
 */
class CostCenterSelectorFormTest extends Unit
{
    protected const string GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH = 'purchasing_control.budget.validation.cost_center_mismatch';

    protected const int ID_COST_CENTER = 1;

    protected const int ID_BUDGET = 2;

    protected const int ID_COST_CENTER_OTHER = 3;

    protected const int ID_BUDGET_OTHER = 4;

    /**
     * Both budgets carry the same label on purpose: equally named budgets of different cost
     * centers must stay selectable.
     */
    protected const string BUDGET_LABEL = 'Travel (€100.00)';

    /**
     * The cost center and the budget are chosen in a single submit, so the budget must survive the
     * submit that assigns the cost center for the first time.
     */
    public function testSubmitKeepsBudgetWhenCostCenterIsSelectedForTheFirstTime(): void
    {
        // Arrange
        $form = $this->createForm();

        // Act
        $form->submit([
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            CostCenterSelectorForm::FIELD_ID_BUDGET => (string)static::ID_BUDGET,
        ]);

        // Assert
        $this->assertTrue($form->isValid());
        $this->assertSame(static::ID_COST_CENTER, $form->getData()[CostCenterSelectorForm::FIELD_ID_COST_CENTER]);
        $this->assertSame(static::ID_BUDGET, $form->getData()[CostCenterSelectorForm::FIELD_ID_BUDGET]);
    }

    public function testSubmitKeepsBudgetWhenCostCenterIsChangedToAnotherOne(): void
    {
        // Arrange
        $form = $this->createForm([
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => static::ID_COST_CENTER,
            CostCenterSelectorForm::FIELD_ID_BUDGET => static::ID_BUDGET,
        ]);

        // Act
        $form->submit([
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER_OTHER,
            CostCenterSelectorForm::FIELD_ID_BUDGET => (string)static::ID_BUDGET_OTHER,
        ]);

        // Assert
        $this->assertTrue($form->isValid());
        $this->assertSame(static::ID_COST_CENTER_OTHER, $form->getData()[CostCenterSelectorForm::FIELD_ID_COST_CENTER]);
        $this->assertSame(static::ID_BUDGET_OTHER, $form->getData()[CostCenterSelectorForm::FIELD_ID_BUDGET]);
    }

    /**
     * The budget field offers the budgets of every cost center, so the pair has to be verified on
     * the server as well.
     */
    public function testSubmitRejectsBudgetOfAnotherCostCenter(): void
    {
        // Arrange
        $form = $this->createForm();

        // Act
        $form->submit([
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            CostCenterSelectorForm::FIELD_ID_BUDGET => (string)static::ID_BUDGET_OTHER,
        ]);

        // Assert
        $this->assertFalse($form->isValid());
        $this->assertSame(
            static::GLOSSARY_KEY_BUDGET_COST_CENTER_MISMATCH,
            $this->getFirstErrorMessage($form, CostCenterSelectorForm::FIELD_ID_BUDGET),
        );
    }

    /**
     * The budget list merges the budgets of every cost center, so it must stay keyed by budget id
     * instead of by label.
     */
    public function testFormOffersEquallyNamedBudgetsOfDifferentCostCenters(): void
    {
        // Arrange
        $form = $this->createForm();

        // Act
        $choiceViews = $form->createView()->children[CostCenterSelectorForm::FIELD_ID_BUDGET]->vars['choices'];

        // Assert
        $this->assertSame(
            [(string)static::ID_BUDGET, (string)static::ID_BUDGET_OTHER],
            array_map(static fn (ChoiceView $choiceView): string => $choiceView->value, $choiceViews),
        );
        $this->assertSame([static::BUDGET_LABEL, static::BUDGET_LABEL], array_map(
            static fn (ChoiceView $choiceView): string => $choiceView->label,
            $choiceViews,
        ));
    }

    public function testSubmitWithoutBudgetKeepsFormValid(): void
    {
        // Arrange
        $form = $this->createForm();

        // Act
        $form->submit([
            CostCenterSelectorForm::FIELD_ID_COST_CENTER => (string)static::ID_COST_CENTER,
            CostCenterSelectorForm::FIELD_ID_BUDGET => '',
        ]);

        // Assert
        $this->assertTrue($form->isValid());
        $this->assertSame(static::ID_COST_CENTER, $form->getData()[CostCenterSelectorForm::FIELD_ID_COST_CENTER]);
        $this->assertNull($form->getData()[CostCenterSelectorForm::FIELD_ID_BUDGET]);
    }

    protected function getFirstErrorMessage(FormInterface $form, string $fieldName): ?string
    {
        foreach ($form->get($fieldName)->getErrors() as $formError) {
            return $formError->getMessage();
        }

        return null;
    }

    /**
     * @param array<string, int|null> $data
     */
    protected function createForm(array $data = []): FormInterface
    {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory()
            ->create(CostCenterSelectorForm::class, $data, [
                CostCenterSelectorForm::OPTION_COST_CENTER_CHOICES => [
                    'Cost center' => static::ID_COST_CENTER,
                    'Other cost center' => static::ID_COST_CENTER_OTHER,
                ],
                CostCenterSelectorForm::OPTION_BUDGET_CHOICES => [static::ID_BUDGET, static::ID_BUDGET_OTHER],
                CostCenterSelectorForm::OPTION_BUDGET_LABELS => [
                    static::ID_BUDGET => static::BUDGET_LABEL,
                    static::ID_BUDGET_OTHER => static::BUDGET_LABEL,
                ],
                CostCenterSelectorForm::OPTION_BUDGET_CHOICE_ATTRS => [
                    static::ID_BUDGET => ['data-cost-center-id' => static::ID_COST_CENTER],
                    static::ID_BUDGET_OTHER => ['data-cost-center-id' => static::ID_COST_CENTER_OTHER],
                ],
                CostCenterSelectorForm::OPTION_BUDGET_COST_CENTER_MAP => [
                    static::ID_BUDGET => static::ID_COST_CENTER,
                    static::ID_BUDGET_OTHER => static::ID_COST_CENTER_OTHER,
                ],
            ]);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Expander;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterCollectionTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Yves\Kernel\PermissionAwareTrait;
use SprykerFeature\Yves\PurchasingControl\Reader\CostCenterReaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CostCenterOrderSearchFormExpander implements CostCenterOrderSearchFormExpanderInterface
{
    use PermissionAwareTrait;

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Form\OrderSearchForm::FIELD_FILTERS
     */
    protected const string FIELD_FILTERS = 'filters';

    protected const string FIELD_COST_CENTER_IDS = 'costCenterIds';

    protected const string FIELD_BUDGET_IDS = 'budgetIds';

    protected const string LABEL_COST_CENTER = 'purchasing_control.selector.label';

    protected const string LABEL_BUDGET = 'purchasing_control.budget.selector.label';

    protected const string PLACEHOLDER_COST_CENTER = 'purchasing_control.selector.placeholder';

    protected const string PLACEHOLDER_BUDGET = 'purchasing_control.budget.selector.placeholder';

    public function __construct(
        protected readonly CustomerClientInterface $customerClient,
        protected readonly CostCenterReaderInterface $costCenterReader,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function expand(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $companyUserTransfer = $this->customerClient->getCustomer()?->getCompanyUserTransfer();

        if ($companyUserTransfer === null) {
            return $builder;
        }

        $costCenterCollectionTransfer = $this->resolveCostCenterCollection($companyUserTransfer);

        $this->addCostCenterField($builder, $costCenterCollectionTransfer);
        $this->addBudgetField($builder, $costCenterCollectionTransfer);

        return $builder;
    }

    protected function resolveCostCenterCollection(CompanyUserTransfer $companyUserTransfer): CostCenterCollectionTransfer
    {
        if ($this->can('SeeCompanyOrdersPermissionPlugin', $companyUserTransfer->getIdCompanyUser())) {
            $idCompany = $companyUserTransfer->getFkCompany() ?: null;

            if ($idCompany !== null) {
                return $this->costCenterReader->getCostCentersWithBudgetsForOrderSearch(null, $idCompany);
            }
        }

        $idCompanyBusinessUnit = $companyUserTransfer->getFkCompanyBusinessUnit() ?: null;

        if ($idCompanyBusinessUnit === null) {
            return new CostCenterCollectionTransfer();
        }

        return $this->costCenterReader->getCostCentersWithBudgetsForOrderSearch($idCompanyBusinessUnit);
    }

    protected function addCostCenterField(
        FormBuilderInterface $builder,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
    ): void {
        $builder->get(static::FIELD_FILTERS)->add(static::FIELD_COST_CENTER_IDS, ChoiceType::class, [
            'choices' => $this->buildCostCenterChoices($costCenterCollectionTransfer),
            'required' => false,
            'label' => static::LABEL_COST_CENTER,
            'placeholder' => static::PLACEHOLDER_COST_CENTER,
            'attr' => [
                'class' => 'form__field col col--sm-12 col--lg-6',
            ],
        ]);
    }

    protected function addBudgetField(
        FormBuilderInterface $builder,
        CostCenterCollectionTransfer $costCenterCollectionTransfer,
    ): void {
        $budgetCostCenterMap = $this->buildBudgetCostCenterMap($costCenterCollectionTransfer);

        $builder->get(static::FIELD_FILTERS)->add(static::FIELD_BUDGET_IDS, ChoiceType::class, [
            'choices' => $this->buildBudgetChoices($costCenterCollectionTransfer),
            'required' => false,
            'label' => static::LABEL_BUDGET,
            'placeholder' => static::PLACEHOLDER_BUDGET,
            'choice_attr' => function (int $idBudget) use ($budgetCostCenterMap): array {
                return ['data-cost-center-id' => (string)($budgetCostCenterMap[$idBudget] ?? '')];
            },
            'attr' => [
                'class' => 'form__field col col--sm-12 col--lg-6',
            ],
        ]);
    }

    /**
     * @return array<string, int>
     */
    protected function buildCostCenterChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $choices[$costCenterTransfer->getNameOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    protected function buildBudgetChoices(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $choices = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
                $choices[$budgetTransfer->getNameOrFail()] = $budgetTransfer->getIdBudgetOrFail();
            }
        }

        return $choices;
    }

    /**
     * @return array<int, int>
     */
    protected function buildBudgetCostCenterMap(CostCenterCollectionTransfer $costCenterCollectionTransfer): array
    {
        $map = [];

        foreach ($costCenterCollectionTransfer->getCostCenters() as $costCenterTransfer) {
            $this->mapBudgetsToCostCenter($costCenterTransfer, $map);
        }

        return $map;
    }

    /**
     * @param array<int, int> $map
     */
    protected function mapBudgetsToCostCenter(CostCenterTransfer $costCenterTransfer, array &$map): void
    {
        foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
            $map[$budgetTransfer->getIdBudgetOrFail()] = $costCenterTransfer->getIdCostCenterOrFail();
        }
    }
}

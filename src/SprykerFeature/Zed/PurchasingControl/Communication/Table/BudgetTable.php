<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Table;

use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetTableMap;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;

class BudgetTable extends AbstractTable
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::indexAction()
     *
     * @var string
     */
    protected const BASE_URL = '/purchasing-control/budget';

    protected const COL_ACTIONS = 'actions';

    protected const COL_PERIOD = 'period';

    protected const COL_STATUS = 'status';

    protected const COL_RULE = 'rule';

    protected const COL_REMAINING_AMOUNT = 'remaining_amount';

    protected const LABEL_ACTIVE = '<span class="badge badge-soft-success">Active</span>';

    protected const LABEL_INACTIVE = '<span class="badge badge-soft-secondary">Inactive</span>';

    protected const PARAM_ID_COST_CENTER = 'id-cost-center';

    protected const PARAM_ID_BUDGET = 'id-budget';

    protected const BUTTON_EDIT = 'Edit';

    protected const URL_BUDGET_EDIT = '/purchasing-control/budget/edit';

    public function __construct(
        protected SpyBudgetQuery $budgetQuery,
        protected int $idCostCenter,
    ) {
        $this->baseUrl = static::BASE_URL;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setUrl(sprintf('table?%s=%d', static::PARAM_ID_COST_CENTER, $this->idCostCenter));

        $config->setHeader([
            SpyBudgetTableMap::COL_ID_BUDGET => 'ID',
            SpyBudgetTableMap::COL_NAME => 'Name',
            SpyBudgetTableMap::COL_AMOUNT => 'Amount',
            static::COL_REMAINING_AMOUNT => 'Remaining Amount',
            static::COL_PERIOD => 'Period',
            static::COL_RULE => 'Rule',
            static::COL_STATUS => 'Status',
            static::COL_ACTIONS => 'Actions',
        ]);

        $config->setSearchable([
            SpyBudgetTableMap::COL_NAME,
        ]);

        $config->setSortable([
            SpyBudgetTableMap::COL_ID_BUDGET,
            SpyBudgetTableMap::COL_NAME,
            static::COL_REMAINING_AMOUNT,
            SpyBudgetTableMap::COL_STARTS_AT,
        ]);

        $config->setRawColumns([
            static::COL_RULE,
            static::COL_STATUS,
            static::COL_ACTIONS,
        ]);

        $config->setDefaultSortField(SpyBudgetTableMap::COL_ID_BUDGET, TableConfiguration::SORT_DESC);

        return $config;
    }

    protected function prepareQuery(): SpyBudgetQuery
    {
        $this->budgetQuery
            ->leftJoinSpyBudgetConsumption()
            ->withColumn('COALESCE(SUM(spy_budget_consumption.amount), 0)', 'ConsumedAmount')
            ->withColumn('spy_budget.amount - COALESCE(SUM(spy_budget_consumption.amount), 0)', static::COL_REMAINING_AMOUNT)
            ->groupByIdBudget()
            ->filterByFkCostCenter($this->idCostCenter);

        return $this->budgetQuery;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array<mixed>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $queryResults = $this->runQuery($this->prepareQuery(), $config);
        $results = [];

        foreach ($queryResults as $item) {
            $results[] = $this->formatRow($item);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    protected function formatRow(array $item): array
    {
        $idBudget = $item[SpyBudgetTableMap::COL_ID_BUDGET];

        return [
            SpyBudgetTableMap::COL_ID_BUDGET => $idBudget,
            SpyBudgetTableMap::COL_NAME => $item[SpyBudgetTableMap::COL_NAME],
            SpyBudgetTableMap::COL_AMOUNT => sprintf(
                '%d %s',
                $item[SpyBudgetTableMap::COL_AMOUNT] / 100,
                $item[SpyBudgetTableMap::COL_CURRENCY_ISO_CODE],
            ),
            static::COL_REMAINING_AMOUNT => sprintf(
                '%d %s',
                $item[static::COL_REMAINING_AMOUNT] / 100,
                $item[SpyBudgetTableMap::COL_CURRENCY_ISO_CODE],
            ),
            static::COL_PERIOD => sprintf(
                '%s – %s',
                $item[SpyBudgetTableMap::COL_STARTS_AT],
                $item[SpyBudgetTableMap::COL_ENDS_AT],
            ),
            static::COL_RULE => $this->getRuleLabel($item[SpyBudgetTableMap::COL_ENFORCEMENT_RULE]),
            static::COL_STATUS => $item[SpyBudgetTableMap::COL_IS_ACTIVE]
                ? static::LABEL_ACTIVE
                : static::LABEL_INACTIVE,
            static::COL_ACTIONS => $this->buildActions($idBudget),
        ];
    }

    protected function getRuleLabel(string $rule): string
    {
        $labelClass = 'badge-soft-info';

        if ($rule === PurchasingControlConfig::ENFORCEMENT_RULE_BLOCK) {
            $labelClass = 'badge-soft-danger';
        }

        if ($rule === PurchasingControlConfig::ENFORCEMENT_RULE_WARN) {
            $labelClass = 'badge-soft-warning';
        }

        return sprintf(
            '<span class="badge %s">%s</span>',
            $labelClass,
            str_replace('_', ' ', ucfirst($rule)),
        );
    }

    protected function buildActions(int $idBudget): string
    {
        $editUrl = $this->buildUrl(
            static::URL_BUDGET_EDIT . '?' . http_build_query([
                static::PARAM_ID_COST_CENTER => $this->idCostCenter,
                static::PARAM_ID_BUDGET => $idBudget,
            ]),
        );

        return sprintf(
            '<a href="%s" class="btn btn-xs btn-primary">%s</a>',
            $editUrl,
            static::BUTTON_EDIT,
        );
    }
}

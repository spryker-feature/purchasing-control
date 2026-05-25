<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Table;

use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetConsumptionTableMap;
use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetTableMap;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\Money\Business\MoneyFacadeInterface;
use SprykerFeature\Shared\PurchasingControl\PurchasingControlConfig;

class BudgetTable extends AbstractTable
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::indexAction()
     *
     * @var string
     */
    protected const string BASE_URL = '/purchasing-control/budget';

    protected const string COL_ACTIONS = 'actions';

    protected const string COL_PERIOD = 'period';

    protected const string COL_STATUS = 'status';

    protected const string COL_RULE = 'rule';

    protected const string COL_REMAINING_AMOUNT = 'remaining_amount';

    protected const string COL_CONSUMED_AMOUNT = 'ConsumedAmount';

    protected const string LABEL_ACTIVE = 'Active';

    protected const string LABEL_INACTIVE = 'Deactivated';

    protected const string LABEL_CLASS_ACTIVE = 'label-success';

    protected const string LABEL_CLASS_INACTIVE = 'label-danger';

    protected const string LABEL_CLASS_RULE_BLOCK = 'label-danger';

    protected const string LABEL_CLASS_RULE_WARN = 'label-warning';

    protected const string LABEL_CLASS_RULE_DEFAULT = 'label-info';

    protected const string LABEL_RULE_BLOCK = 'Block Order';

    protected const string LABEL_RULE_WARN = 'Display Warning';

    protected const string PARAM_ID_COST_CENTER = 'id-cost-center';

    protected const string PARAM_ID_BUDGET = 'id-budget';

    protected const string BUTTON_EDIT = 'Edit';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::editAction()
     */
    protected const string URL_BUDGET_EDIT = '/purchasing-control/budget/edit';

    public function __construct(
        protected SpyBudgetQuery $budgetQuery,
        protected int $idCostCenter,
        protected MoneyFacadeInterface $moneyFacade,
        protected UtilDateTimeServiceInterface $utilDateTimeService,
    ) {
        $this->baseUrl = static::BASE_URL;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setUrl($this->buildTableUrl());
        $config->setHeader($this->getTableHeaders());

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

    protected function buildTableUrl(): string
    {
        return Url::generate('/table', [static::PARAM_ID_COST_CENTER => $this->idCostCenter])->build();
    }

    /**
     * @return array<string, string>
     */
    protected function getTableHeaders(): array
    {
        return [
            SpyBudgetTableMap::COL_ID_BUDGET => 'ID',
            SpyBudgetTableMap::COL_NAME => 'Name',
            SpyBudgetTableMap::COL_AMOUNT => 'Amount',
            static::COL_REMAINING_AMOUNT => 'Remaining Amount',
            static::COL_PERIOD => 'Period',
            static::COL_RULE => 'Rule',
            static::COL_STATUS => 'Status',
            static::COL_ACTIONS => 'Actions',
        ];
    }

    protected function prepareQuery(): SpyBudgetQuery
    {
        $this->budgetQuery
            ->leftJoinSpyBudgetConsumption()
            ->withColumn(
                sprintf('COALESCE(SUM(%s), 0)', SpyBudgetConsumptionTableMap::COL_AMOUNT),
                static::COL_CONSUMED_AMOUNT,
            )
            ->withColumn(
                sprintf('%s - COALESCE(SUM(%s), 0)', SpyBudgetTableMap::COL_AMOUNT, SpyBudgetConsumptionTableMap::COL_AMOUNT),
                static::COL_REMAINING_AMOUNT,
            )
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
            SpyBudgetTableMap::COL_AMOUNT => $this->formatMoney(
                (int)$item[SpyBudgetTableMap::COL_AMOUNT],
                $item[SpyBudgetTableMap::COL_CURRENCY_ISO_CODE],
            ),
            static::COL_REMAINING_AMOUNT => $this->formatMoney(
                (int)$item[static::COL_REMAINING_AMOUNT],
                $item[SpyBudgetTableMap::COL_CURRENCY_ISO_CODE],
            ),
            static::COL_PERIOD => sprintf(
                '%s – %s',
                $this->utilDateTimeService->formatDate($item[SpyBudgetTableMap::COL_STARTS_AT]),
                $this->utilDateTimeService->formatDate($item[SpyBudgetTableMap::COL_ENDS_AT]),
            ),
            static::COL_RULE => $this->getRuleLabel($item[SpyBudgetTableMap::COL_ENFORCEMENT_RULE]),
            static::COL_STATUS => $item[SpyBudgetTableMap::COL_IS_ACTIVE]
                ? $this->generateLabel(static::LABEL_ACTIVE, static::LABEL_CLASS_ACTIVE)
                : $this->generateLabel(static::LABEL_INACTIVE, static::LABEL_CLASS_INACTIVE),
            static::COL_ACTIONS => $this->buildActions($idBudget),
        ];
    }

    protected function getRuleLabel(string $rule): string
    {
        if ($rule === PurchasingControlConfig::ENFORCEMENT_RULE_BLOCK) {
            return $this->generateLabel(static::LABEL_RULE_BLOCK, static::LABEL_CLASS_RULE_BLOCK);
        }

        if ($rule === PurchasingControlConfig::ENFORCEMENT_RULE_WARN) {
            return $this->generateLabel(static::LABEL_RULE_WARN, static::LABEL_CLASS_RULE_WARN);
        }

        return $this->generateLabel(str_replace('_', ' ', ucfirst($rule)), static::LABEL_CLASS_RULE_DEFAULT);
    }

    protected function formatMoney(int $amount, string $currencyIsoCode): string
    {
        return $this->moneyFacade->formatWithSymbol(
            $this->moneyFacade->fromInteger($amount, $currencyIsoCode),
        );
    }

    protected function buildActions(int $idBudget): string
    {
        $editUrl = Url::generate(static::URL_BUDGET_EDIT, [
            static::PARAM_ID_COST_CENTER => $this->idCostCenter,
            static::PARAM_ID_BUDGET => $idBudget,
        ]);

        return $this->generateEditButton($editUrl, static::BUTTON_EDIT);
    }
}

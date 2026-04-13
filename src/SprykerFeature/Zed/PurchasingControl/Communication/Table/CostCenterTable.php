<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Table;

use Orm\Zed\PurchasingControl\Persistence\Map\SpyCostCenterTableMap;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class CostCenterTable extends AbstractTable
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\CostCenterController::indexAction()
     *
     * @var string
     */
    protected const BASE_URL = '/purchasing-control/cost-center';

    protected const COL_ACTIONS = 'actions';

    protected const COL_STATUS = 'status';

    protected const COL_BUSINESS_UNITS = 'business_units';

    protected const COL_BUDGETS = 'budgets';

    protected const VISIBLE_LIMIT = 3;

    protected const LABEL_ACTIVE = '<span class="badge badge-soft-success">Active</span>';

    protected const LABEL_INACTIVE = '<span class="badge badge-soft-secondary">Inactive</span>';

    protected const BUTTON_EDIT = 'Edit';

    protected const BUTTON_BUDGETS = 'Budgets';

    protected const URL_EDIT = '/purchasing-control/cost-center/edit';

    protected const URL_BUDGET = '/purchasing-control/budget';

    protected const PARAM_ID_COST_CENTER = 'id-cost-center';

    public function __construct(
        protected SpyCostCenterQuery $costCenterQuery,
        protected SpyBudgetQuery $budgetQuery,
        protected UtilDateTimeServiceInterface $utilDateTimeService,
    ) {
        $this->baseUrl = static::BASE_URL;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setUrl('table');

        $config->setHeader([
            SpyCostCenterTableMap::COL_ID_COST_CENTER => 'ID',
            SpyCostCenterTableMap::COL_NAME => 'Name',
            static::COL_BUSINESS_UNITS => 'Business Units',
            static::COL_BUDGETS => 'Budgets',
            static::COL_STATUS => 'Status',
            SpyCostCenterTableMap::COL_CREATED_AT => 'Created At',
            static::COL_ACTIONS => 'Actions',
        ]);

        $config->setSearchable([
            SpyCostCenterTableMap::COL_NAME,
        ]);

        $config->setSortable([
            SpyCostCenterTableMap::COL_ID_COST_CENTER,
            SpyCostCenterTableMap::COL_NAME,
            SpyCostCenterTableMap::COL_CREATED_AT,
        ]);

        $config->setRawColumns([
            static::COL_STATUS,
            static::COL_BUSINESS_UNITS,
            static::COL_BUDGETS,
            static::COL_ACTIONS,
        ]);

        $config->setDefaultSortField(SpyCostCenterTableMap::COL_ID_COST_CENTER, TableConfiguration::SORT_DESC);

        return $config;
    }

    protected function prepareQuery(): SpyCostCenterQuery
    {
        return $this->costCenterQuery;
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
        $idCostCenter = (int)$item[SpyCostCenterTableMap::COL_ID_COST_CENTER];

        return [
            SpyCostCenterTableMap::COL_ID_COST_CENTER => $idCostCenter,
            SpyCostCenterTableMap::COL_NAME => $item[SpyCostCenterTableMap::COL_NAME],
            static::COL_BUSINESS_UNITS => $this->renderBusinessUnits($idCostCenter),
            static::COL_BUDGETS => $this->renderBudgets($idCostCenter),
            static::COL_STATUS => $item[SpyCostCenterTableMap::COL_IS_ACTIVE]
                ? static::LABEL_ACTIVE
                : static::LABEL_INACTIVE,
            SpyCostCenterTableMap::COL_CREATED_AT => $this->utilDateTimeService->formatDateTime(
                $item[SpyCostCenterTableMap::COL_CREATED_AT],
            ),
            static::COL_ACTIONS => $this->buildActions($idCostCenter),
        ];
    }

    protected function renderBusinessUnits(int $idCostCenter): string
    {
        $junctions = $this->costCenterQuery::create()
            ->filterByIdCostCenter($idCostCenter)
            ->leftJoinWithSpyCostCenterToCompanyBusinessUnit()
            ->leftJoinWith('SpyCostCenterToCompanyBusinessUnit.SpyCompanyBusinessUnit')
            ->find();

        $names = [];
        foreach ($junctions as $costCenter) {
            foreach ($costCenter->getSpyCostCenterToCompanyBusinessUnits() as $junction) {
                $bu = $junction->getSpyCompanyBusinessUnit();
                if ($bu !== null) {
                    $names[] = htmlspecialchars($bu->getName());
                }
            }
        }

        return $this->renderNameList($names);
    }

    protected function renderBudgets(int $idCostCenter): string
    {
        $names = $this->budgetQuery::create()
            ->filterByFkCostCenter($idCostCenter)
            ->select(['Name'])
            ->find()
            ->getData();

        $names = array_map('htmlspecialchars', $names);

        return $this->renderNameList($names);
    }

    /**
     * @param array<string> $names
     */
    protected function renderNameList(array $names): string
    {
        $total = count($names);

        if ($total <= static::VISIBLE_LIMIT) {
            return implode(', ', $names);
        }

        $visible = array_slice($names, 0, static::VISIBLE_LIMIT);
        $hidden = $total - static::VISIBLE_LIMIT;

        return sprintf('%s <span class="badge badge-soft-secondary">+%d more</span>', implode(', ', $visible), $hidden);
    }

    protected function buildActions(int $idCostCenter): string
    {
        $editUrl = $this->buildUrl(static::URL_EDIT . '?' . http_build_query([static::PARAM_ID_COST_CENTER => $idCostCenter]));
        $budgetUrl = $this->buildUrl(static::URL_BUDGET . '?' . http_build_query([static::PARAM_ID_COST_CENTER => $idCostCenter]));

        return sprintf(
            '<a href="%s" class="btn btn-xs btn-primary">%s</a> <a href="%s" class="btn btn-xs btn-default">%s</a>',
            $editUrl,
            static::BUTTON_EDIT,
            $budgetUrl,
            static::BUTTON_BUDGETS,
        );
    }
}

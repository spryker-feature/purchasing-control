<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Table;

use Orm\Zed\PurchasingControl\Persistence\Map\SpyBudgetTableMap;
use Orm\Zed\PurchasingControl\Persistence\Map\SpyCostCenterTableMap;
use Orm\Zed\PurchasingControl\Persistence\SpyBudgetQuery;
use Orm\Zed\PurchasingControl\Persistence\SpyCostCenterQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class CostCenterTable extends AbstractTable
{
    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\CostCenterController::indexAction()
     *
     * @var string
     */
    protected const string BASE_URL = '/purchasing-control/cost-center';

    protected const string COL_ACTIONS = 'actions';

    protected const string COL_STATUS = 'status';

    protected const string COL_BUSINESS_UNITS = 'business_units';

    protected const string COL_BUDGETS = 'budgets';

    protected const int VISIBLE_LIMIT = 3;

    protected const string LABEL_ACTIVE = 'Active';

    protected const string LABEL_INACTIVE = 'Deactivated';

    protected const string LABEL_CLASS_ACTIVE = 'label-success';

    protected const string LABEL_CLASS_INACTIVE = 'label-danger';

    protected const string BUTTON_EDIT = 'Edit';

    protected const string BUTTON_BUDGETS = 'Budgets';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\CostCenterController::editAction()
     */
    protected const string URL_EDIT = '/purchasing-control/cost-center/edit';

    /**
     * @uses \SprykerFeature\Zed\PurchasingControl\Communication\Controller\BudgetController::indexAction()
     */
    protected const string URL_BUDGET = '/purchasing-control/budget';

    protected const string PARAM_ID_COST_CENTER = 'id-cost-center';

    public function __construct(
        protected SpyCostCenterQuery $costCenterQuery,
        protected SpyBudgetQuery $budgetQuery,
        protected UtilDateTimeServiceInterface $utilDateTimeService,
    ) {
        $this->baseUrl = static::BASE_URL;
    }

    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setUrl($this->buildTableUrl());
        $config->setHeader($this->getTableHeaders());

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

    protected function buildTableUrl(): string
    {
        return Url::generate('/table', $this->getRequest()->query->all())->build();
    }

    /**
     * @return array<string, string>
     */
    protected function getTableHeaders(): array
    {
        return [
            SpyCostCenterTableMap::COL_ID_COST_CENTER => 'ID',
            SpyCostCenterTableMap::COL_NAME => 'Name',
            static::COL_BUSINESS_UNITS => 'Business Units',
            static::COL_BUDGETS => 'Budgets',
            static::COL_STATUS => 'Status',
            SpyCostCenterTableMap::COL_CREATED_AT => 'Created At',
            static::COL_ACTIONS => 'Actions',
        ];
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

        $costCenterIds = array_column((array)$queryResults, SpyCostCenterTableMap::COL_ID_COST_CENTER);
        $businessUnitNamesByCostCenterId = $this->getBusinessUnitNamesByCostCenterIds($costCenterIds);
        $budgetNamesByCostCenterId = $this->getBudgetNamesByCostCenterIds($costCenterIds);

        $results = [];
        foreach ($queryResults as $item) {
            $results[] = $this->formatRow($item, $businessUnitNamesByCostCenterId, $budgetNamesByCostCenterId);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<int, array<string>> $businessUnitNamesByCostCenterId
     * @param array<int, array<string>> $budgetNamesByCostCenterId
     *
     * @return array<string, mixed>
     */
    protected function formatRow(array $item, array $businessUnitNamesByCostCenterId, array $budgetNamesByCostCenterId): array
    {
        $idCostCenter = (int)$item[SpyCostCenterTableMap::COL_ID_COST_CENTER];

        return [
            SpyCostCenterTableMap::COL_ID_COST_CENTER => $idCostCenter,
            SpyCostCenterTableMap::COL_NAME => $item[SpyCostCenterTableMap::COL_NAME],
            static::COL_BUSINESS_UNITS => $this->renderNameList($businessUnitNamesByCostCenterId[$idCostCenter] ?? []),
            static::COL_BUDGETS => $this->renderNameList($budgetNamesByCostCenterId[$idCostCenter] ?? []),
            static::COL_STATUS => $item[SpyCostCenterTableMap::COL_IS_ACTIVE]
                ? $this->generateLabel(static::LABEL_ACTIVE, static::LABEL_CLASS_ACTIVE)
                : $this->generateLabel(static::LABEL_INACTIVE, static::LABEL_CLASS_INACTIVE),
            SpyCostCenterTableMap::COL_CREATED_AT => $this->utilDateTimeService->formatDateTime(
                $item[SpyCostCenterTableMap::COL_CREATED_AT],
            ),
            static::COL_ACTIONS => $this->buildActions($idCostCenter),
        ];
    }

    /**
     * @param array<int> $costCenterIds
     *
     * @return array<int, array<string>>
     */
    protected function getBusinessUnitNamesByCostCenterIds(array $costCenterIds): array
    {
        if (!$costCenterIds) {
            return [];
        }

        $costCenterEntities = $this->costCenterQuery::create()
            ->filterByIdCostCenter($costCenterIds, Criteria::IN)
            ->leftJoinWithSpyCostCenterToCompanyBusinessUnit()
            ->useSpyCostCenterToCompanyBusinessUnitQuery()
                ->leftJoinWithSpyCompanyBusinessUnit()
            ->endUse()
            ->find();

        $namesByCostCenterId = [];
        foreach ($costCenterEntities as $costCenterEntity) {
            $idCostCenter = $costCenterEntity->getIdCostCenter();
            $namesByCostCenterId[$idCostCenter] = [];

            foreach ($costCenterEntity->getSpyCostCenterToCompanyBusinessUnits() as $junctionEntity) {
                $businessUnitEntity = $junctionEntity->getSpyCompanyBusinessUnit();

                if ($businessUnitEntity === null) {
                    continue;
                }

                $namesByCostCenterId[$idCostCenter][] = htmlspecialchars($businessUnitEntity->getName());
            }
        }

        return $namesByCostCenterId;
    }

    /**
     * @param array<int> $costCenterIds
     *
     * @return array<int, array<string>>
     */
    protected function getBudgetNamesByCostCenterIds(array $costCenterIds): array
    {
        if (!$costCenterIds) {
            return [];
        }

        $budgets = $this->budgetQuery::create()
            ->filterByFkCostCenter($costCenterIds, Criteria::IN)
            ->select([SpyBudgetTableMap::COL_FK_COST_CENTER, SpyBudgetTableMap::COL_NAME])
            ->find()
            ->getData();

        $namesByCostCenterId = [];
        foreach ($budgets as $budget) {
            $idCostCenter = (int)$budget[SpyBudgetTableMap::COL_FK_COST_CENTER];
            $namesByCostCenterId[$idCostCenter][] = htmlspecialchars($budget[SpyBudgetTableMap::COL_NAME]);
        }

        return $namesByCostCenterId;
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
        $editUrl = Url::generate(static::URL_EDIT, [static::PARAM_ID_COST_CENTER => $idCostCenter]);
        $budgetUrl = Url::generate(static::URL_BUDGET, [static::PARAM_ID_COST_CENTER => $idCostCenter]);

        return $this->generateEditButton($editUrl, static::BUTTON_EDIT)
            . ' '
            . $this->generateButton($budgetUrl, static::BUTTON_BUDGETS, ['class' => 'btn-default', 'icon' => 'fa-money']);
    }
}

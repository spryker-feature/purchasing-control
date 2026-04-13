<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider;

use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\CostCenterForm;
use SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig;

class CostCenterFormDataProvider
{
    public function __construct(
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected PurchasingControlConfig $config,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            CostCenterForm::OPTION_BUSINESS_UNIT_CHOICES => $this->getBusinessUnitChoices(),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function getBusinessUnitChoices(): array
    {
        // Projects with large BU datasets should override `getBusinessUnitSelectLimit()` or switch to async autocomplete
        $paginationTransfer = (new PaginationTransfer())
            ->setPage(1)
            ->setMaxPerPage($this->config->getBusinessUnitSelectLimit());

        $criteriaFilterTransfer = (new CompanyBusinessUnitCriteriaFilterTransfer())
            ->setPagination($paginationTransfer);

        $companyBusinessUnitCollection = $this->companyBusinessUnitFacade
            ->getCompanyBusinessUnitCollection($criteriaFilterTransfer);

        $choices = [];

        foreach ($companyBusinessUnitCollection->getCompanyBusinessUnits() as $companyBusinessUnit) {
            $choices[$companyBusinessUnit->getNameOrFail()] = $companyBusinessUnit->getIdCompanyBusinessUnitOrFail();
        }

        return $choices;
    }
}

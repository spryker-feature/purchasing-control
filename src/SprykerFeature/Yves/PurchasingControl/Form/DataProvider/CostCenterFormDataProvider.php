<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Form\DataProvider;

use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\FilterTransfer;
use Spryker\Client\CompanyBusinessUnit\CompanyBusinessUnitClientInterface;
use SprykerFeature\Yves\PurchasingControl\Form\CostCenterForm;
use SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig;

class CostCenterFormDataProvider
{
    public function __construct(
        protected readonly CompanyBusinessUnitClientInterface $companyBusinessUnitClient,
        protected readonly PurchasingControlConfig $config,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(int $idCompany): array
    {
        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitClient->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())
                ->setIdCompany($idCompany)
                ->setFilter(
                    (new FilterTransfer())->setLimit($this->config->getCompanyBusinessUnitLimitForCostCenterForm()),
                ),
        );

        $businessUnitChoices = [];
        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $businessUnitChoices[$companyBusinessUnitTransfer->getNameOrFail()] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        return [
            CostCenterForm::OPTION_BUSINESS_UNIT_CHOICES => $businessUnitChoices,
        ];
    }

    public function getData(?CostCenterTransfer $costCenterTransfer = null): ?CostCenterTransfer
    {
        return $costCenterTransfer;
    }
}

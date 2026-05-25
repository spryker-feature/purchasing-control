<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Form\DataProvider;

use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Zed\PurchasingControl\Communication\Form\CostCenterForm;

class CostCenterFormDataProvider
{
    public function __construct(
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(?CostCenterTransfer $costCenterTransfer = null): array
    {
        $companyBusinessUnitIds = $costCenterTransfer ? $costCenterTransfer->getCompanyBusinessUnitIds() : [];
        $companyBusinessUnitCollectionTransfer = $this->getCompanyBusinessUnitCollectionTransfer(
            $companyBusinessUnitIds,
        );

        return [
            CostCenterForm::OPTION_COMPANY_CHOICES => $this->getAssignedCompanies($companyBusinessUnitCollectionTransfer),
            CostCenterForm::OPTION_BUSINESS_UNIT_CHOICES => $this->getBusinessUnitChoices($companyBusinessUnitCollectionTransfer),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getAssignedCompanies(CompanyBusinessUnitCollectionTransfer $companyBusinessUnitCollectionTransfer): array
    {
        $companyChoices = [];
        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $idCompany = $companyBusinessUnitTransfer->getFkCompany();
            if ($idCompany === null) {
                continue;
            }

            $companyName = $companyBusinessUnitTransfer->getCompany()?->getName();
            $companyChoices[sprintf('%s (ID: %s)', $companyName, $idCompany)] = $idCompany;
        }

        return $companyChoices;
    }

    /**
     * @param array<string, mixed> $formOptions
     * @param array<string, mixed> $submittedData
     *
     * @return array<string, mixed>
     */
    public function expandOptionsWithSubmittedData(array $formOptions, array $submittedData): array
    {
        $submittedBuIds = $submittedData[CostCenterForm::FIELD_COMPANY_BUSINESS_UNIT_IDS] ?? [];

        if ($submittedBuIds) {
            $companyBusinessUnitCollectionTransfer = $this->getCompanyBusinessUnitCollectionTransfer(
                array_map('intval', (array)$submittedBuIds),
            );

            $formOptions[CostCenterForm::OPTION_BUSINESS_UNIT_CHOICES] = $this->getBusinessUnitChoices($companyBusinessUnitCollectionTransfer);
            $formOptions[CostCenterForm::OPTION_COMPANY_CHOICES] = $this->getAssignedCompanies($companyBusinessUnitCollectionTransfer);
        }

        return $formOptions;
    }

    /**
     * @param array<int> $companyBusinessUnitIds
     *
     * @return \Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer
     */
    protected function getCompanyBusinessUnitCollectionTransfer(array $companyBusinessUnitIds): CompanyBusinessUnitCollectionTransfer
    {
        if (!$companyBusinessUnitIds) {
            return new CompanyBusinessUnitCollectionTransfer();
        }

        return $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->setCompanyBusinessUnitIds($companyBusinessUnitIds),
        );
    }

    /**
     * @return array<string, int>
     */
    protected function getBusinessUnitChoices(CompanyBusinessUnitCollectionTransfer $companyBusinessUnitCollectionTransfer): array
    {
        $companyBusinessUnitChoices = [];
        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $companyBusinessUnitChoices[sprintf(
                '%s (ID: %s)',
                $companyBusinessUnitTransfer->getNameOrFail(),
                $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail(),
            )] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        return $companyBusinessUnitChoices;
    }
}

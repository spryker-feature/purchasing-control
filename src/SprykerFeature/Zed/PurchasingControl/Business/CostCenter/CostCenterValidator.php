<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterCollectionRequestTransfer;
use Generated\Shared\Transfer\CostCenterCollectionResponseTransfer;
use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlRepositoryInterface;

class CostCenterValidator implements CostCenterValidatorInterface
{
    protected const string GLOSSARY_KEY_VALIDATION_NAME_EMPTY = 'purchasing_control.cost_center.validation.name_empty';

    protected const string GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY = 'purchasing_control.cost_center.validation.business_unit_empty';

    protected const string GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED = 'purchasing_control.cost_center.validation.access_denied';

    protected const string GLOSSARY_KEY_BU_NOT_IN_COMPANY = 'purchasing_control.cost_center.validation.business_unit_not_in_company';

    public function __construct(
        protected CostCenterReaderInterface $costCenterReader,
        protected PurchasingControlRepositoryInterface $repository,
    ) {
    }

    public function validate(CostCenterTransfer $costCenterTransfer, ?int $idCompany = null): ?ErrorTransfer
    {
        if ($idCompany !== null && $this->isExistingCostCenterAccessDenied($costCenterTransfer, $idCompany)) {
            return (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED)
                ->setEntityIdentifier((string)$costCenterTransfer->getIdCostCenter());
        }

        if ($idCompany !== null && $this->hasBusinessUnitsOutsideCompany($costCenterTransfer, $idCompany)) {
            return (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_BU_NOT_IN_COMPANY)
                ->setEntityIdentifier((string)$costCenterTransfer->getIdCostCenter());
        }

        if (!$costCenterTransfer->getName()) {
            return (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_NAME_EMPTY)
                ->setEntityIdentifier((string)$costCenterTransfer->getIdCostCenter());
        }

        if (!$costCenterTransfer->getCompanyBusinessUnitIds()) {
            return (new ErrorTransfer())
                ->setMessage(static::GLOSSARY_KEY_VALIDATION_BUSINESS_UNIT_EMPTY)
                ->setEntityIdentifier((string)$costCenterTransfer->getIdCostCenter());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function validateCostCenterCollection(
        CostCenterCollectionRequestTransfer $costCenterCollectionRequestTransfer,
        CostCenterCollectionResponseTransfer $costCenterCollectionResponseTransfer,
    ): array {
        $idCompany = $costCenterCollectionRequestTransfer->getCustomer()?->getCompanyUserTransfer()?->getFkCompany();
        $invalidIndices = [];

        /** @var \ArrayObject<int, \Generated\Shared\Transfer\CostCenterTransfer> $costCenterTransfers */
        $costCenterTransfers = $costCenterCollectionRequestTransfer->getCostCenters();

        foreach ($costCenterTransfers as $index => $costCenterTransfer) {
            $errorTransfer = $this->validate($costCenterTransfer, $idCompany);

            if ($errorTransfer === null) {
                continue;
            }

            $invalidIndices[$index] = true;
            $costCenterCollectionResponseTransfer->addError($errorTransfer);
        }

        return $invalidIndices;
    }

    protected function isExistingCostCenterAccessDenied(CostCenterTransfer $costCenterTransfer, int $idCompany): bool
    {
        if ($costCenterTransfer->getIdCostCenter() === null) {
            return false;
        }

        $costCenterCriteriaTransfer = (new CostCenterCriteriaTransfer())
            ->setCostCenterConditions(
                (new CostCenterConditionsTransfer())
                    ->addIdCostCenter($costCenterTransfer->getIdCostCenterOrFail())
                    ->addIdCompany($idCompany),
            );

        return $this->costCenterReader->getCostCenterCollection($costCenterCriteriaTransfer)->getCostCenters()->count() === 0;
    }

    protected function hasBusinessUnitsOutsideCompany(CostCenterTransfer $costCenterTransfer, int $idCompany): bool
    {
        $submittedCompanyBusinessUnitIds = $costCenterTransfer->getCompanyBusinessUnitIds();

        if ($submittedCompanyBusinessUnitIds === []) {
            return false;
        }

        $verifiedCompanyBusinessUnitIds = $this->repository->getCompanyBusinessUnitIdsForCompany($idCompany, $submittedCompanyBusinessUnitIds);

        return count($verifiedCompanyBusinessUnitIds) !== count($submittedCompanyBusinessUnitIds);
    }
}

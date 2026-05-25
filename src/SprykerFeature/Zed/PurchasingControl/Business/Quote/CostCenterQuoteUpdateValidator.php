<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\Quote;

use Generated\Shared\Transfer\CostCenterConditionsTransfer;
use Generated\Shared\Transfer\CostCenterCriteriaTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateRequestTransfer;
use Generated\Shared\Transfer\CostCenterQuoteUpdateResponseTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\PurchasingControl\Business\CostCenter\CostCenterReaderInterface;

class CostCenterQuoteUpdateValidator implements CostCenterQuoteUpdateValidatorInterface
{
    protected const string GLOSSARY_KEY_QUOTE_APPROVED = 'purchasing_control.error.quote_approved';

    /**
     * @uses \Spryker\Shared\QuoteApproval\QuoteApprovalConfig::STATUS_APPROVED
     */
    protected const string QUOTE_APPROVAL_STATUS_APPROVED = 'approved';

    protected const string GLOSSARY_KEY_NOT_COMPANY_USER = 'purchasing_control.error.not_company_user';

    protected const string GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED = 'purchasing_control.error.cost_center_access_denied';

    protected const string GLOSSARY_KEY_BUDGET_ACCESS_DENIED = 'purchasing_control.error.budget_access_denied';

    public function __construct(protected readonly CostCenterReaderInterface $costCenterReader)
    {
    }

    public function validate(
        CostCenterQuoteUpdateRequestTransfer $costCenterQuoteUpdateRequestTransfer,
        QuoteTransfer $quoteTransfer
    ): CostCenterQuoteUpdateResponseTransfer {
        $costCenterQuoteUpdateResponseTransfer = (new CostCenterQuoteUpdateResponseTransfer())->setIsSuccessful(true);

        if ($this->isQuoteApproved($quoteTransfer)) {
            return $costCenterQuoteUpdateResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new ErrorTransfer())->setMessage(static::GLOSSARY_KEY_QUOTE_APPROVED));
        }

        $companyUserTransfer = $costCenterQuoteUpdateRequestTransfer->getCustomer()?->getCompanyUserTransfer();
        if ($companyUserTransfer === null) {
            return $costCenterQuoteUpdateResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new ErrorTransfer())->setMessage(static::GLOSSARY_KEY_NOT_COMPANY_USER));
        }

        $costCenterConditionsTransfer = (new CostCenterConditionsTransfer())
            ->addIdCostCenter($costCenterQuoteUpdateRequestTransfer->getIdCostCenterOrFail())
            ->addIdCompanyBusinessUnit($companyUserTransfer->getFkCompanyBusinessUnitOrFail())
            ->setIsActive(true)
            ->setWithBudgets($costCenterQuoteUpdateRequestTransfer->getIdBudget() !== null);

        $costCenterTransfers = $this->costCenterReader
            ->getCostCenterCollection(
                (new CostCenterCriteriaTransfer())->setCostCenterConditions($costCenterConditionsTransfer),
            )
            ->getCostCenters();

        if ($costCenterTransfers->count() === 0) {
            return $costCenterQuoteUpdateResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new ErrorTransfer())->setMessage(static::GLOSSARY_KEY_COST_CENTER_ACCESS_DENIED));
        }

        if ($costCenterQuoteUpdateRequestTransfer->getIdBudget() !== null && !$this->isBudgetBelongsToCostCenter($costCenterTransfers->offsetGet(0), $costCenterQuoteUpdateRequestTransfer->getIdBudgetOrFail())) {
            return $costCenterQuoteUpdateResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new ErrorTransfer())->setMessage(static::GLOSSARY_KEY_BUDGET_ACCESS_DENIED));
        }

        return $costCenterQuoteUpdateResponseTransfer;
    }

    protected function isQuoteApproved(QuoteTransfer $quoteTransfer): bool
    {
        foreach ($quoteTransfer->getQuoteApprovals() as $quoteApprovalTransfer) {
            if ($quoteApprovalTransfer->getStatus() === static::QUOTE_APPROVAL_STATUS_APPROVED) {
                return true;
            }
        }

        return false;
    }

    protected function isBudgetBelongsToCostCenter(CostCenterTransfer $costCenterTransfer, int $idBudget): bool
    {
        foreach ($costCenterTransfer->getBudgets() as $budgetTransfer) {
            if ($budgetTransfer->getIdBudget() === $idBudget) {
                return true;
            }
        }

        return false;
    }
}

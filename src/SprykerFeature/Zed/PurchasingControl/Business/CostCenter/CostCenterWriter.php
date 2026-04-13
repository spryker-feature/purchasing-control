<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business\CostCenter;

use Generated\Shared\Transfer\CostCenterResponseTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use SprykerFeature\Zed\PurchasingControl\Persistence\PurchasingControlEntityManagerInterface;

class CostCenterWriter implements CostCenterWriterInterface
{
    protected const ERROR_NAME_EMPTY = 'Cost center name must not be empty.';

    public function __construct(protected PurchasingControlEntityManagerInterface $costCenterEntityManager)
    {
    }

    public function createCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer
    {
        $costCenterResponseTransfer = $this->validate($costCenterTransfer);

        if (!$costCenterResponseTransfer->getIsSuccessful()) {
            return $costCenterResponseTransfer;
        }

        $costCenterTransfer = $this->costCenterEntityManager->createCostCenter($costCenterTransfer);

        return $costCenterResponseTransfer
            ->setIsSuccessful(true)
            ->setCostCenter($costCenterTransfer);
    }

    public function updateCostCenter(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer
    {
        $costCenterResponseTransfer = $this->validate($costCenterTransfer);

        if (!$costCenterResponseTransfer->getIsSuccessful()) {
            return $costCenterResponseTransfer;
        }

        $costCenterTransfer = $this->costCenterEntityManager->updateCostCenter($costCenterTransfer);

        return $costCenterResponseTransfer
            ->setIsSuccessful(true)
            ->setCostCenter($costCenterTransfer);
    }

    protected function validate(CostCenterTransfer $costCenterTransfer): CostCenterResponseTransfer
    {
        $costCenterResponseTransfer = (new CostCenterResponseTransfer())->setIsSuccessful(true);

        if (!$costCenterTransfer->getName()) {
            $costCenterResponseTransfer
                ->setIsSuccessful(false)
                ->addError((new MessageTransfer())->setValue(static::ERROR_NAME_EMPTY));
        }

        return $costCenterResponseTransfer;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\PurchasingControl\Controller;

use ArrayObject;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CostCenterTransfer;
use Spryker\Yves\Kernel\Controller\AbstractController;
use Spryker\Yves\Kernel\PermissionAwareTrait;
use SprykerFeature\Shared\PurchasingControl\Plugin\Permission\ManageCostCentersPermissionPlugin;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlFactory getFactory()
 * @method \SprykerFeature\Yves\PurchasingControl\PurchasingControlConfig getConfig()
 */
abstract class AbstractPurchasingControlController extends AbstractController
{
    use PermissionAwareTrait;

    protected const string GLOSSARY_KEY_ACCESS_DENIED = 'purchasing_control.cost_center.access.denied';

    protected const string GLOSSARY_KEY_COMPANY_USER_NOT_FOUND = 'purchasing_control.cost_center.error.company_user_not_found';

    protected const string GLOSSARY_KEY_COST_CENTER_NOT_FOUND = 'purchasing_control.budget.error.cost_center_not_found';

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function requireAuthorizedCompanyUser(): CompanyUserTransfer
    {
        if (!$this->can(ManageCostCentersPermissionPlugin::KEY)) {
            throw new AccessDeniedHttpException(static::GLOSSARY_KEY_ACCESS_DENIED);
        }

        $companyUserTransfer = $this->getFactory()->getCustomerClient()->getCustomer()?->getCompanyUserTransfer();

        if (!$companyUserTransfer?->getFkCompany()) {
            throw new NotFoundHttpException(static::GLOSSARY_KEY_COMPANY_USER_NOT_FOUND);
        }

        return $companyUserTransfer;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function requireCostCenterForCompany(string $costCenterUuid, int $idCompany): CostCenterTransfer
    {
        $costCenterTransfer = $this->getFactory()
            ->createCostCenterReader()
            ->findCostCenter($costCenterUuid, $idCompany);

        if (!$costCenterTransfer) {
            throw new NotFoundHttpException(static::GLOSSARY_KEY_COST_CENTER_NOT_FOUND);
        }

        return $costCenterTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ErrorTransfer> $errorTransfers
     */
    protected function addErrorMessages(ArrayObject $errorTransfers): void
    {
        foreach ($errorTransfers as $errorTransfer) {
            $this->addErrorMessage($errorTransfer->getMessageOrFail());
        }
    }
}

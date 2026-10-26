<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Communication\Plugin\AclMerchantPortal;

use Generated\Shared\Transfer\AclEntityMetadataConfigTransfer;
use Generated\Shared\Transfer\AclEntityMetadataTransfer;
use Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\AclEntityConfigurationExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerFeature\Zed\PurchasingControl\Business\PurchasingControlFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\PurchasingControl\PurchasingControlConfig getConfig()
 * @method \SprykerFeature\Zed\PurchasingControl\Communication\PurchasingControlCommunicationFactory getFactory()
 */
class PurchasingControlAclEntityConfigurationExpanderPlugin extends AbstractPlugin implements AclEntityConfigurationExpanderPluginInterface
{
    /**
     * @uses \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_READ
     */
    protected const int OPERATION_MASK_READ = 0b1;

    /**
     * @uses \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_UPDATE
     */
    protected const int OPERATION_MASK_UPDATE = 0b100;

    /**
     * @uses \Spryker\Shared\AclEntity\AclEntityConstants::OPERATION_MASK_DELETE
     */
    protected const int OPERATION_MASK_DELETE = 0b1000;

    protected const string ENTITY_BUDGET_CONSUMPTION = 'Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumption';

    /**
     * {@inheritDoc}
     * - Grants global read, update and delete access to `SpyBudgetConsumption`, so the budget restore OMS commands
     *   (`RestoreBudgetOnCancelOmsCommandPlugin`, `RestoreBudgetOnRefundOmsCommandPlugin`) triggered by a merchant user can adjust it.
     *
     * @api
     */
    public function expand(AclEntityMetadataConfigTransfer $aclEntityMetadataConfigTransfer): AclEntityMetadataConfigTransfer
    {
        $aclEntityMetadataConfigTransfer
            ->getAclEntityMetadataCollectionOrFail()
            ->addAclEntityMetadata(
                static::ENTITY_BUDGET_CONSUMPTION,
                (new AclEntityMetadataTransfer())
                    ->setEntityName(static::ENTITY_BUDGET_CONSUMPTION)
                    ->setDefaultGlobalOperationMask(static::OPERATION_MASK_READ | static::OPERATION_MASK_UPDATE | static::OPERATION_MASK_DELETE),
            );

        return $aclEntityMetadataConfigTransfer;
    }
}

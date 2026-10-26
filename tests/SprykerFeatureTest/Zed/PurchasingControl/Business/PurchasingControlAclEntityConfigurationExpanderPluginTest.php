<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\PurchasingControl\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AclEntityMetadataCollectionTransfer;
use Generated\Shared\Transfer\AclEntityMetadataConfigTransfer;
use SprykerFeature\Zed\PurchasingControl\Communication\Plugin\AclMerchantPortal\PurchasingControlAclEntityConfigurationExpanderPlugin;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group PurchasingControl
 * @group Business
 * @group PurchasingControlAclEntityConfigurationExpanderPluginTest
 */
class PurchasingControlAclEntityConfigurationExpanderPluginTest extends Unit
{
    protected const string ENTITY_BUDGET_CONSUMPTION = 'Orm\Zed\PurchasingControl\Persistence\SpyBudgetConsumption';

    /**
     * Read (0b1) | update (0b100) | delete (0b1000), see {@link \Spryker\Shared\AclEntity\AclEntityConstants}.
     */
    protected const int OPERATION_MASK_READ_UPDATE_DELETE = 0b1101;

    public function testExpandGrantsGlobalReadUpdateDeleteOnBudgetConsumption(): void
    {
        // Arrange
        $aclEntityMetadataConfigTransfer = (new AclEntityMetadataConfigTransfer())
            ->setAclEntityMetadataCollection(new AclEntityMetadataCollectionTransfer());

        // Act
        $aclEntityMetadataConfigTransfer = (new PurchasingControlAclEntityConfigurationExpanderPlugin())->expand($aclEntityMetadataConfigTransfer);

        // Assert
        $aclEntityMetadataTransfer = $aclEntityMetadataConfigTransfer
            ->getAclEntityMetadataCollectionOrFail()
            ->getCollection()[static::ENTITY_BUDGET_CONSUMPTION];

        $this->assertSame(static::ENTITY_BUDGET_CONSUMPTION, $aclEntityMetadataTransfer->getEntityName());
        $this->assertSame(static::OPERATION_MASK_READ_UPDATE_DELETE, $aclEntityMetadataTransfer->getDefaultGlobalOperationMask());
        $this->assertNull($aclEntityMetadataTransfer->getParent());
    }
}

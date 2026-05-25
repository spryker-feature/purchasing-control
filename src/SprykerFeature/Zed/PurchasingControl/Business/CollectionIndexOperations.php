<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\PurchasingControl\Business;

use ArrayObject;

class CollectionIndexOperations
{
    /**
     * @param \ArrayObject<int, mixed> $itemTransfers
     * @param array<int, true> $invalidIndices
     *
     * @return array<\ArrayObject<int, mixed>>
     */
    public function splitByInvalidIndices(ArrayObject $itemTransfers, array $invalidIndices): array
    {
        $validItemTransfers = new ArrayObject();
        $invalidItemTransfers = new ArrayObject();

        foreach ($itemTransfers as $index => $itemTransfer) {
            if (isset($invalidIndices[$index])) {
                $invalidItemTransfers->offsetSet($index, $itemTransfer);

                continue;
            }

            $validItemTransfers->offsetSet($index, $itemTransfer);
        }

        return [$validItemTransfers, $invalidItemTransfers];
    }

    /**
     * @param \ArrayObject<int, mixed> $validItemTransfers
     * @param \ArrayObject<int, mixed> $invalidItemTransfers
     *
     * @return \ArrayObject<int, mixed>
     */
    public function mergeItems(ArrayObject $validItemTransfers, ArrayObject $invalidItemTransfers): ArrayObject
    {
        $mergedTransfers = new ArrayObject();

        foreach ($validItemTransfers as $index => $itemTransfer) {
            $mergedTransfers->offsetSet($index, $itemTransfer);
        }

        foreach ($invalidItemTransfers as $index => $itemTransfer) {
            $mergedTransfers->offsetSet($index, $itemTransfer);
        }

        return $mergedTransfers;
    }
}

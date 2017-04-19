<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductGroupCollector\Business\Collector\Storage;

use Generated\Shared\Transfer\ProductGroupTransfer;
use Spryker\Shared\ProductGroup\ProductGroupConfig;
use Spryker\Zed\Collector\Business\Collector\Storage\AbstractStoragePropelCollector;

class ProductGroupCollector extends AbstractStoragePropelCollector
{

    /**
     * @return string
     */
    protected function collectResourceType()
    {
        return ProductGroupConfig::RESOURCE_TYPE_PRODUCT_GROUP;
    }

    /**
     * @param string $touchKey
     * @param array $collectItemData
     *
     * @return array
     */
    protected function collectItem($touchKey, array $collectItemData)
    {
        $productGroupTransfer = new ProductGroupTransfer();
        $productGroupTransfer->fromArray($collectItemData, true);

        return $productGroupTransfer->modifiedToArray();
    }

    /**
     * @return bool
     */
    protected function isStorageTableJoinWithLocaleEnabled()
    {
        return true;
    }

}

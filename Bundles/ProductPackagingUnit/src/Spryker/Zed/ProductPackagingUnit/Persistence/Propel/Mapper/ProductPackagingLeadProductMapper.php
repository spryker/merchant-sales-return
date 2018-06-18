<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductPackagingUnit\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\ProductPackagingLeadProductTransfer;
use Orm\Zed\ProductPackagingUnit\Persistence\SpyProductPackagingLeadProduct;

class ProductPackagingLeadProductMapper implements ProductPackagingLeadProductMapperInterface
{
    /**
     * @param \Orm\Zed\ProductPackagingUnit\Persistence\SpyProductPackagingLeadProduct $productPackagingLeadProductEntity
     * @param \Generated\Shared\Transfer\ProductPackagingLeadProductTransfer $productPackagingLeadProductTransfer
     *
     * @return \Generated\Shared\Transfer\ProductPackagingLeadProductTransfer
     */
    public function mapProductPackagingLeadProductTransfer(
        SpyProductPackagingLeadProduct $productPackagingLeadProductEntity,
        ProductPackagingLeadProductTransfer $productPackagingLeadProductTransfer
    ): ProductPackagingLeadProductTransfer {
        $productPackagingLeadProductTransfer->setIdProduct($productPackagingLeadProductEntity->getFkProduct());
        $productPackagingLeadProductTransfer->setIdProductAbstract($productPackagingLeadProductEntity->getFkProductAbstract());

        return $productPackagingLeadProductTransfer;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShoppingListProductOption\Dependency\Facade;

use Generated\Shared\Transfer\ProductOptionCollectionTransfer;
use Generated\Shared\Transfer\ProductOptionCriteriaTransfer;

class ShoppingListProductOptionToProductOptionFacadeBridge implements ShoppingListProductOptionToProductOptionFacadeInterface
{
    /**
     * @var \Spryker\Zed\ProductOption\Business\ProductOptionFacadeInterface
     */
    protected $productOptionFacade;

    /**
     * @param \Spryker\Zed\ProductOption\Business\ProductOptionFacadeInterface $productOptionFacade
     */
    public function __construct($productOptionFacade)
    {
        $this->productOptionFacade = $productOptionFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOptionCriteriaTransfer $productOptionCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductOptionCollectionTransfer
     */
    public function getProductOptionCollectionByProductOptionCriteria(ProductOptionCriteriaTransfer $productOptionCriteriaTransfer): ProductOptionCollectionTransfer
    {
        return $this->productOptionFacade
            ->getProductOptionCollectionByProductOptionCriteria($productOptionCriteriaTransfer);
    }
}

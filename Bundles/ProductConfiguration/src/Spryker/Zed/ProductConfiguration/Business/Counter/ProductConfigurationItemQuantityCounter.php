<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductConfiguration\Business\Counter;

use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CartItemQuantityTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductConfigurationInstanceTransfer;
use Spryker\Service\ProductConfiguration\ProductConfigurationServiceInterface;

class ProductConfigurationItemQuantityCounter implements ProductConfigurationItemQuantityCounterInterface
{
    protected const DEFAULT_ITEM_QUANTITY = 0;

    /**
     * @uses \Spryker\Zed\Cart\CartConfig::OPERATION_REMOVE
     */
    protected const OPERATION_REMOVE = 'remove';

    /**
     * @var \Spryker\Service\ProductConfiguration\ProductConfigurationServiceInterface
     */
    protected $productConfigurationService;

    /**
     * @param \Spryker\Service\ProductConfiguration\ProductConfigurationServiceInterface $productConfigurationService
     */
    public function __construct(ProductConfigurationServiceInterface $productConfigurationService)
    {
        $this->productConfigurationService = $productConfigurationService;
    }

    /**
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return \Generated\Shared\Transfer\CartItemQuantityTransfer
     */
    public function countItemQuantity(
        CartChangeTransfer $cartChangeTransfer,
        ItemTransfer $itemTransfer
    ): CartItemQuantityTransfer {
        $currentItemQuantity = static::DEFAULT_ITEM_QUANTITY;
        $quoteItems = $cartChangeTransfer->getQuote()->getItems();
        $cartChangeItemsTransfer = $cartChangeTransfer->getItems();

        foreach ($quoteItems as $quoteItemTransfer) {
            if ($this->isSameItem($quoteItemTransfer, $itemTransfer)) {
                $currentItemQuantity += $quoteItemTransfer->getQuantity();
            }
        }

        foreach ($cartChangeItemsTransfer as $cartChangeItemTransfer) {
            if ($this->isSameProductConfigurationItem($cartChangeItemTransfer, $itemTransfer)) {
                $currentItemQuantity = $this->changeItemQuantityAccordingToOperation(
                    $currentItemQuantity,
                    $cartChangeItemTransfer->getQuantity(),
                    $cartChangeTransfer->getOperation()
                );
            }
        }

        return (new CartItemQuantityTransfer())->setQuantity($currentItemQuantity);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemInCartTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return bool
     */
    protected function isSameItem(
        ItemTransfer $itemInCartTransfer,
        ItemTransfer $itemTransfer
    ): bool {
        return $itemInCartTransfer->getSku() === $itemTransfer->getSku()
            && $this->isSameProductConfigurationItem($itemInCartTransfer, $itemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemInCartTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return bool
     */
    protected function isSameProductConfigurationItem(ItemTransfer $itemInCartTransfer, ItemTransfer $itemTransfer): bool
    {
        $itemInCartProductConfigurationInstanceTransfer = $itemInCartTransfer->getProductConfigurationInstance();
        $itemProductConfigurationInstanceTransfer = $itemTransfer->getProductConfigurationInstance();

        return ($itemInCartProductConfigurationInstanceTransfer === null && $itemProductConfigurationInstanceTransfer === null)
            || $this->isProductConfigurationInstanceHashEquals(
                $itemInCartProductConfigurationInstanceTransfer,
                $itemProductConfigurationInstanceTransfer
            );
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer|null $itemInCartProductConfigurationInstanceTransfer
     * @param \Generated\Shared\Transfer\ProductConfigurationInstanceTransfer|null $itemProductConfigurationInstanceTransfer
     *
     * @return bool
     */
    protected function isProductConfigurationInstanceHashEquals(
        ?ProductConfigurationInstanceTransfer $itemInCartProductConfigurationInstanceTransfer,
        ?ProductConfigurationInstanceTransfer $itemProductConfigurationInstanceTransfer
    ): bool {
        if ($itemInCartProductConfigurationInstanceTransfer === null || $itemProductConfigurationInstanceTransfer === null) {
            return false;
        }

        return $this->productConfigurationService->getProductConfigurationInstanceHash($itemInCartProductConfigurationInstanceTransfer)
            === $this->productConfigurationService->getProductConfigurationInstanceHash($itemProductConfigurationInstanceTransfer);
    }

    /**
     * @param int $currentItemQuantity
     * @param int|null $deltaQuantity
     * @param string|null $operation
     *
     * @return int
     */
    protected function changeItemQuantityAccordingToOperation(int $currentItemQuantity, ?int $deltaQuantity, ?string $operation): int
    {
        if ($operation === static::OPERATION_REMOVE) {
            return $currentItemQuantity - $deltaQuantity;
        }

        return $currentItemQuantity + $deltaQuantity;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesOrder\Business;

use Generated\Shared\Transfer\MerchantOrderCollectionTransfer;
use Generated\Shared\Transfer\MerchantOrderCriteriaFilterTransfer;
use Generated\Shared\Transfer\MerchantOrderTransfer;
use Generated\Shared\Transfer\OrderTransfer;

interface MerchantSalesOrderFacadeInterface
{
    /**
     * Specification:
     * - Iterates through the order items of given order looking for merchant reference presence.
     * - Skips all the order items without merchant reference.
     * - Creates a new merchant order for each unique merchant reference found.
     * - Creates a new merchant order item for each order item and assign it to a merchant order accordingly.
     * - Creates a new merchant order total and assign it to a merchant order accordingly.
     * - Returns a collection of merchant orders filled with merchant order items and merchant order totals.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantOrderCollectionTransfer
     */
    public function createMerchantSalesOrders(OrderTransfer $orderTransfer): MerchantOrderCollectionTransfer;

    /**
     * Specification:
     * - Finds all the merchant orders using MerchantOrderCriteriaFilterTransfer.
     * - Returns a collection of found merchant orders.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MerchantOrderCriteriaFilterTransfer $merchantCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantOrderCollectionTransfer
     */
    public function getMerchantOrderCollection(
        MerchantOrderCriteriaFilterTransfer $merchantCriteriaFilterTransfer
    ): MerchantOrderCollectionTransfer;

    /**
     * Specification:
     * - Returns a merchant order found using MerchantOrderCriteriaFilterTransfer.
     * - Returns NULL if merchant order is not found.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MerchantOrderCriteriaFilterTransfer $merchantCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantOrderTransfer|null
     */
    public function findMerchantOrder(
        MerchantOrderCriteriaFilterTransfer $merchantCriteriaFilterTransfer
    ): ?MerchantOrderTransfer;
}

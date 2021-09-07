<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturn;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\MerchantSalesReturn\Dependency\Facade\MerchantSalesReturnToMerchantSalesOrderFacadeBridge;

/**
 * @method \Spryker\Zed\MerchantSalesReturn\MerchantSalesReturnConfig getConfig()
 */
class MerchantSalesReturnDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_MERCHANT_SALES_ORDER = 'FACADE_MERCHANT_SALES_ORDER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addMerchantSalesOrderFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantSalesOrderFacade(Container $container): Container
    {
        $container->set(static::FACADE_MERCHANT_SALES_ORDER, function (Container $container) {
            return new MerchantSalesReturnToMerchantSalesOrderFacadeBridge($container->getLocator()->merchantSalesOrder()->facade());
        });

        return $container;
    }
}

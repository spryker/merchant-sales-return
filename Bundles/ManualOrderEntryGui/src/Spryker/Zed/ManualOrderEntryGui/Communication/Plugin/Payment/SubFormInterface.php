<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\Payment;

interface SubFormInterface
{
    const OPTIONS_FIELD_NAME = 'select_options';

    /**
     * @return string
     */
    public function getPropertyPath();

    /**
     * @return string
     */
    public function getName();
}

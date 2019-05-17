<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Scheduler\Business\Clean;

use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerTransfer;

interface SchedulerCleanInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerTransfer $schedulerTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function clean(SchedulerTransfer $schedulerTransfer): SchedulerResponseTransfer;
}

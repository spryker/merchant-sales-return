<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\GuiTable\Communication\Setter;

use Generated\Shared\Transfer\GuiTableConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableDataSourceConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableFiltersConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableItemSelectionConfigurationTransfer;
use Generated\Shared\Transfer\GuiTablePaginationConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableRowActionsConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableSearchConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableSyncStateUrlConfigurationTransfer;
use Spryker\Zed\GuiTable\GuiTableConfig;

class ConfigurationDefaultValuesSetter implements ConfigurationDefaultValuesSetterInterface
{
    /**
     * @var \Spryker\Zed\GuiTable\GuiTableConfig
     */
    protected $guiTableConfig;

    /**
     * @param \Spryker\Zed\GuiTable\GuiTableConfig $guiTableConfig
     */
    public function __construct(GuiTableConfig $guiTableConfig)
    {
        $this->guiTableConfig = $guiTableConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    public function setDefaultValues(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableConfigurationTransfer = $this->setDefaultDataSource($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultRowActions($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultPagination($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultSearch($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultFilters($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultItemSelection($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->setDefaultSyncStateUrl($guiTableConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultDataSource(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableDataSourceConfigurationTransfer = $guiTableConfigurationTransfer->getDataSource() ?? new GuiTableDataSourceConfigurationTransfer();

        if ($guiTableDataSourceConfigurationTransfer->getType() === null) {
            $guiTableDataSourceConfigurationTransfer->setType($this->guiTableConfig->getDefaultDataSourceType());
        }

        $guiTableConfigurationTransfer->setDataSource($guiTableDataSourceConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultRowActions(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableRowActionsConfigurationTransfer = $guiTableConfigurationTransfer->getRowActions() ?? new GuiTableRowActionsConfigurationTransfer();

        if ($guiTableRowActionsConfigurationTransfer->getIsEnabled() === null) {
            $guiTableRowActionsConfigurationTransfer->setIsEnabled(
                in_array('rowActions', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $guiTableConfigurationTransfer->setRowActions($guiTableRowActionsConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultPagination(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTablePaginationConfigurationTransfer = $guiTableConfigurationTransfer->getPagination() ?? new GuiTablePaginationConfigurationTransfer();

        if (empty($guiTablePaginationConfigurationTransfer->getSizes())) {
            $guiTablePaginationConfigurationTransfer->setSizes($this->guiTableConfig->getDefaultAvailablePageSizes());
        }

        if ($guiTablePaginationConfigurationTransfer->getIsEnabled() === null) {
            $guiTablePaginationConfigurationTransfer->setIsEnabled(
                in_array('pagination', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $guiTableConfigurationTransfer->setPagination($guiTablePaginationConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultSearch(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableSearchConfigurationTransfer = $guiTableConfigurationTransfer->getSearch() ?? new GuiTableSearchConfigurationTransfer();

        if ($guiTableSearchConfigurationTransfer->getIsEnabled() === null) {
            $guiTableSearchConfigurationTransfer->setIsEnabled(
                in_array('search', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $searchOptions = $guiTableSearchConfigurationTransfer->getSearch();
        $searchOptions['placeholder'] = $searchOptions['placeholder'] ?? $this->guiTableConfig->getDefaultSearchPlaceholder();
        $guiTableSearchConfigurationTransfer->setSearch($searchOptions);

        $guiTableConfigurationTransfer->setSearch($guiTableSearchConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultFilters(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableFiltersConfigurationTransfer = $guiTableConfigurationTransfer->getFilters() ?? new GuiTableFiltersConfigurationTransfer();

        if ($guiTableFiltersConfigurationTransfer->getIsEnabled() === null) {
            $guiTableFiltersConfigurationTransfer->setIsEnabled(
                in_array('filters', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $guiTableConfigurationTransfer->setFilters($guiTableFiltersConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultItemSelection(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableItemSelectionConfigurationTransfer = $guiTableConfigurationTransfer->getItemSelection() ?? new GuiTableItemSelectionConfigurationTransfer();

        if ($guiTableItemSelectionConfigurationTransfer->getIsEnabled() === null) {
            $guiTableItemSelectionConfigurationTransfer->setIsEnabled(
                in_array('itemSelection', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $guiTableConfigurationTransfer->setItemSelection($guiTableItemSelectionConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function setDefaultSyncStateUrl(
        GuiTableConfigurationTransfer $guiTableConfigurationTransfer
    ): GuiTableConfigurationTransfer {
        $guiTableSyncStateUrlConfigurationTransfer = $guiTableConfigurationTransfer->getSyncStateUrl() ?? new GuiTableSyncStateUrlConfigurationTransfer();

        if ($guiTableSyncStateUrlConfigurationTransfer->getIsEnabled() === null) {
            $guiTableSyncStateUrlConfigurationTransfer->setIsEnabled(
                in_array('syncStateUrl', $this->guiTableConfig->getDefaultEnabledFeatures())
            );
        }

        $guiTableConfigurationTransfer->setSyncStateUrl($guiTableSyncStateUrlConfigurationTransfer);

        return $guiTableConfigurationTransfer;
    }
}

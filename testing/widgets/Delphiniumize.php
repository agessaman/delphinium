<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS  
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.                
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */
namespace Delphinium\Testing\Widgets;

use Backend\Classes\WidgetBase;

class Delphiniumize extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $theme;

    public $noRecordsMessage = 'rainlab.builder::lang.database.no_records';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('delphiniumize', $this->getRenderData());
    }

    /**
     * Returns information about this widget, including name and description.
     */
    public function widgetDetails() {}

    public function updateList()
    {
        return ['#'.$this->getId('database-table-list') => $this->makePartial('items', $this->getRenderData())];
    }

    public function refreshActivePlugin()
    {
        return ['#'.$this->getId('body') => $this->makePartial('widget-contents', $this->getRenderData())];
    }

    /*
     * Event handlers
     */

    public function onUpdate()
    {
        return $this->updateList();
    }

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        return $this->updateList();
    }

    /*
     * Methods for the internal use
     */

    protected function getData($pluginVector)
    {
        if (!$pluginVector) {
            return [];
        }

        $pluginCode = $pluginVector->pluginCodeObj->toCode();

        if (!$pluginCode) {
            return [];
        }

        $tables = $this->getTableList($pluginCode);
        $searchTerm = Str::lower($this->getSearchTerm());

        // Apply the search
        //
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($tables as $table) {
                if ($this->textMatchesSearch($words, $table)) {
                    $result[] = $table;
                }
            }

            $tables = $result;
        }

        return $tables;
    }

    protected function getTableList($pluginCode)
    {
        $result = DatabaseTableModel::listPluginTables($pluginCode);

        return $result;
    }

    protected function getRenderData()
    {
        return "my data to render";
        $activePluginVector = $this->controller->getBuilderActivePluginVector();

        return [
            'pluginVector'=>$activePluginVector,
            'items'=>$this->getData($activePluginVector)
        ];
    }
}
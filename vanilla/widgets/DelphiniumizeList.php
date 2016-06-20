<?php
/* Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
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
namespace Delphinium\Vanilla\Widgets;

use Str;
use Lang;
use Input;
use Request;
use Response;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Cms\Classes\ComponentHelpers;
use Backend\Classes\WidgetBase;

/**
 * Component list widget.
 *
 * @package Delphinium\Vanilla
 * @author Damaris Zarco, based on the ComponentList widget by Alexey Bobkov, Samuel Georges
 */
class DelphiniumizeList extends WidgetBase
{
    protected $searchTerm = false;

    protected $groupStatusCache = false;

    protected $pluginComponentList;

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
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        return $this->makePartial('body', [
            'data' => $this->getData($activePluginVector),
            'pluginVector'=>$activePluginVector
        ]);
    }

    /**
     * Returns information about this widget, including name and description.
     */
    public function widgetDetails()
    {
    }

    /*
     * Event handlers
     */

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));

        return $this->updateList();
    }

    public function onGroupStatusUpdate()
    {
        $this->setGroupStatus(Input::get('group'), Input::get('status'));
    }

    /*
     * Methods for th internal use
     */

    protected function getData($activePluginVector)
    {
        if(!$activePluginVector)
        {
            return;
        }
        $searchTerm = Str::lower($this->getSearchTerm());
        $searchWords = [];
        if (strlen($searchTerm)) {
            $searchWords = explode(' ', $searchTerm);
        }

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $this->prepareComponentList();

        $items = [];
        foreach ($plugins as $plugin) {
            $components = $this->getPluginComponents($plugin);
            if (!is_array($components)) {
                continue;
            }

            $pluginDetails = $plugin->pluginDetails();

            $pluginName = isset($pluginDetails['name'])
                ? $pluginDetails['name']
                : Lang::get('system::lang.plugin.unnamed');

            $pluginIcon = isset($pluginDetails['icon'])
                ? $pluginDetails['icon']
                : 'icon-puzzle-piece';

            $pluginDescription = isset($pluginDetails['description'])
                ? $pluginDetails['description']
                : null;

            $pluginClass = get_class($plugin);

            $pluginItems = [];
            foreach ($components as $componentInfo) {
                $className = $componentInfo->className;
                $alias = $componentInfo->alias;
                $component = new $className();

                if ($component->isHidden) {
                    continue;
                }

                $componentDetails = $component->componentDetails();
                $component->alias = '--alias--';

                $item = (object)[
                    'title'          => ComponentHelpers::getComponentName($component),
                    'description'    => ComponentHelpers::getComponentDescription($component),
                    'plugin'         => $pluginName,
                    'propertyConfig' => ComponentHelpers::getComponentsPropertyConfig($component),
                    'propertyValues' => ComponentHelpers::getComponentPropertyValues($component, $alias),
                    'className'      => get_class($component),
                    'pluginIcon'     => $pluginIcon,
                    'alias'          => $alias,
                    'name'           => $componentInfo->duplicateAlias
                        ? $componentInfo->className
                        : $componentInfo->alias
                ];

                if ($searchWords && !$this->itemMatchesSearch($searchWords, $item)) {
                    continue;
                }

                if (!array_key_exists($pluginClass, $items)) {
                    $group = (object)[
                        'title'       => $pluginName,
                        'description' => $pluginDescription,
                        'pluginClass' => $pluginClass,
                        'icon'        => $pluginIcon,
                        'items'       => []
                    ];

                    $items[$pluginClass] = $group;
                }

                $pluginItems[] = $item;
            }

            usort($pluginItems, function ($a, $b) {
                return strcmp($a->title, $b->title);
            });

            if (isset($items[$pluginClass])) {
                $items[$pluginClass]->items = $pluginItems;
            }
        }

        uasort($items, function ($a, $b) {
            return strcmp($a->title, $b->title);
        });

        return $items;
    }

    protected function prepareComponentList()
    {
        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $componentList = [];
        foreach ($plugins as $plugin) {
            $components = $plugin->registerComponents();
            if (!is_array($components)) {
                continue;
            }

            foreach ($components as $className => $alias) {
                $duplicateAlias = false;
                foreach ($componentList as $componentInfo) {
                    if ($componentInfo->alias == $alias) {
                        $componentInfo->duplicateAlias = true;
                        $duplicateAlias = true;
                    }
                }

                $componentList[] = (object)[
                    'className'      => $className,
                    'alias'          => $alias,
                    'duplicateAlias' => $duplicateAlias,
                    'pluginClass'    => get_class($plugin)
                ];
            }
        }

        $this->pluginComponentList = $componentList;
    }

    protected function getPluginComponents($plugin)
    {
        $result = array();
        $pluginClass = get_class($plugin);
        foreach ($this->pluginComponentList as $componentInfo) {
            if ($componentInfo->pluginClass == $pluginClass) {
                $result[] = $componentInfo;
            }
        }

        return $result;
    }

    protected function getSearchTerm()
    {
        return $this->searchTerm !== false ? $this->searchTerm : $this->getSession('search');
    }

    protected function setSearchTerm($term)
    {
        $this->searchTerm = trim($term);
        $this->putSession('search', $this->searchTerm);
    }

    protected function updateList()
    {
        return ['#'.$this->getId('component-list') => $this->makePartial('items', ['items'=>$this->getData()])];
    }

    public function refreshActivePlugin()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        return ['#'.$this->getId('body') => $this->makePartial('body', ['data'=>$this->getData($activePluginVector), 'pluginVector'=>$activePluginVector])];
    }

    protected function itemMatchesSearch(&$words, $item)
    {
        foreach ($words as $word) {
            $word = trim($word);
            if (!strlen($word)) {
                continue;
            }

            if (!$this->itemContainsWord($word, $item)) {
                return false;
            }
        }

        return true;
    }

    protected function itemContainsWord($word, $item)
    {
        if (Str::contains(Str::lower($item->title), $word)) {
            return true;
        }

        if (Str::contains(Str::lower($item->description), $word) && strlen($item->description)) {
            return true;
        }

        if (Str::contains(Str::lower($item->plugin), $word) && strlen($item->plugin)) {
            return true;
        }

        return false;
    }

    protected function getGroupStatuses()
    {
        if ($this->groupStatusCache !== false) {
            return $this->groupStatusCache;
        }

        $groups = $this->getSession('groups');
        if (!is_array($groups)) {
            return $this->groupStatusCache = [];
        }

        return $this->groupStatusCache = $groups;
    }

    protected function setGroupStatus($group, $status)
    {
        $statuses = $this->getGroupStatuses();
        $statuses[$group] = $status;
        $this->groupStatusCache = $statuses;

        $this->putSession('groups', $statuses);
    }

    protected function getGroupStatus($group)
    {
        $statuses = $this->getGroupStatuses();
        if (array_key_exists($group, $statuses)) {
            return $statuses[$group];
        }

        return false;
    }
}

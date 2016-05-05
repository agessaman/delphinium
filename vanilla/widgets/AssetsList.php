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
use Backend\Classes\WidgetBase;
use RainLab\Pages\Classes\PageList as StaticPageList;
use Cms\Classes\Theme;
use RainLab\Pages\Classes\Menu;

/**
 * Static page list widget.
 *
 * @package rainlab\pages
 * @author Alexey Bobkov, Samuel Georges
 */
class AssetsList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\CollapsableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $theme;

    protected $dataIdPrefix;

    /**
     * @var string Message to display when the Delete button is clicked.
     */
    public $deleteConfirmation = 'rainlab.pages::lang.page.delete_confirmation';

    public $noRecordsMessage = 'rainlab.pages::lang.page.no_records';

    public $addSubpageLabel = 'rainlab.pages::lang.page.add_subpage';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;
        $this->theme = Theme::getEditTheme();
        $this->dataIdPrefix = 'page-'.$this->theme->getDirName();
        $this->addSubpageLabel = trans($this->addSubpageLabel);

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', [
            'data' => $this->getData()
        ]);
    }

    /**
     * Returns information about this widget, including name and description.
     */
    public function widgetDetails() {}

    /*
     * Event handlers
     */

    public function onReorder()
    {
        $structure = json_decode(Input::get('structure'), true);
        if (!$structure) {
            throw new SystemException('Invalid structure data posted.');
        }

        $pageList = new StaticPageList($this->theme);
        $pageList->updateStructure($structure);
    }

    public function onUpdate()
    {
        $this->extendSelection();

        return $this->updateList();
    }

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        $this->extendSelection();

        return $this->updateList();
    }

    /*
     * Methods for th internal use
     */

    protected function getData()
    {
        /*
         * Load the data
         */
        $items = call_user_func($this->dataSource);

        $normalizedItems = [];
        foreach ($items as $item) {
            if ($this->suppressDirectories) {
                $fileName = $item->getBaseFileName();
                $dir = dirname($fileName);

                if (in_array($dir, $this->suppressDirectories)) {
                    continue;
                }
            }

            $normalizedItems[] = $this->normalizeItem($item);
        }

        usort($normalizedItems, function ($a, $b) {
            return strcmp($a->fileName, $b->fileName);
        });

        /*
         * Apply the search
         */
        $filteredItems = [];
        $searchTerm = Str::lower($this->getSearchTerm());

        if (strlen($searchTerm)) {
            /*
             * Exact
             */
            foreach ($normalizedItems as $index => $item) {
                if ($this->itemContainsWord($searchTerm, $item, true)) {
                    $filteredItems[] = $item;
                    unset($normalizedItems[$index]);
                }
            }

            /*
             * Fuzzy
             */
            $words = explode(' ', $searchTerm);
            foreach ($normalizedItems as $item) {
                if ($this->itemMatchesSearch($words, $item)) {
                    $filteredItems[] = $item;
                }
            }
        }
        else {
            $filteredItems = $normalizedItems;
        }

        /*
         * Group the items
         */
        $result = [];
        $foundGroups = [];
        foreach ($filteredItems as $itemData) {
            $pos = strpos($itemData->fileName, '/');

            if ($pos !== false) {
                $group = substr($itemData->fileName, 0, $pos);
                if (!array_key_exists($group, $foundGroups)) {
                    $newGroup = (object)[
                        'title' => $group,
                        'items' => []
                    ];

                    $foundGroups[$group] = $newGroup;
                }

                $foundGroups[$group]->items[] = $itemData;
            }
            else {
                $result[] = $itemData;
            }
        }

        foreach ($foundGroups as $group) {
            $result[] = $group;
        }

        return $result;
    }

    protected function getThemeSessionKey($prefix)
    {
        return $prefix.$this->theme->getDirName();
    }

    protected function updateList()
    {
        return ['#'.$this->getId('page-list') => $this->makePartial('items', ['items' => $this->getData()])];
    }

    protected function subtreeToText($page)
    {
        $result = $this->pageToText($page->page);

        $iterator = function($pages) use (&$iterator, &$result) {
            foreach ($pages as $page) {
                $result .= ' '.$this->pageToText($page->page);
                $iterator($page->subpages);
            }
        };

        $iterator($page->subpages);

        return $result;
    }

    protected function pageToText($page)
    {
        $viewBag = $page->getViewBag();

        return $page->getViewBag()->property('title').' '.$page->getViewBag()->property('url');
    }

    protected function getSession($key = null, $default = null)
    {
        $key = strlen($key) ? $this->getThemeSessionKey($key) : $key;

        return parent::getSession($key, $default);
    }

    protected function putSession($key, $value)
    {
        return parent::putSession($this->getThemeSessionKey($key), $value);
    }
}

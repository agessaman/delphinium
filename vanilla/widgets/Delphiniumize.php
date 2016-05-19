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
namespace Delphinium\Vanilla\Widgets;

use Backend\Classes\WidgetBase;
use System\Classes\UpdateManager;
use October\Rain\Support\Str;
use Delphinium\Vanilla\Templates\Component;
use Delphinium\Vanilla\Templates\Plugin;
use Delphinium\Vanilla\Templates\Controller;
use Delphinium\Vanilla\Templates\Model;
use Delphinium\Vanilla\Classes\PluginNodeVisitor;
use Delphinium\Vanilla\Classes\ControllerNodeVisitor;
use Delphinium\Vanilla\Classes\ComponentNodeVisitor;
use October\Rain\Filesystem\Filesystem;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use System\Classes\PluginManager;
use Cms\Classes\ComponentHelpers;
use Yaml;
use Flash;
use Input;
use Delphinium\Vanilla\Classes\Plugin as PluginClass;


class Delphiniumize extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $plugin;
    protected $groupStatusCache = false;
    protected $selectedFilesCache = false;
    protected $controller;
    public $noRecordsMessage = 'rainlab.builder::lang.database.no_records';

    protected $newPluginData;
    protected $readyVars;
    protected $fileVersions;
    protected $pluginManager;

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        $this->relativePluginDir ='/plugins/';
        $this->pluginDir =base_path().'/plugins/';
        $this->alias = $alias;

        $this->plugin = PluginClass::load('/plugins/');
        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        //return $this->makePartial('delphiniumize');

//        return [
//            'pluginVector'=>$activePluginVector,
//            'items'=>$this->getData($activePluginVector)
//        ];
//
        $activePluginVector = $this->controller->getBuilderActivePluginVector();

        return $this->makePartial('body', [
            'data' => $this->getData(),
            'pluginVector'=>$activePluginVector
        ]);
    }


    public function onGroupStatusUpdate()
    {
        $this->setGroupStatus(Input::get('group'), Input::get('status'));
    }

    protected function getData()
    {
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

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        $this->extendSelection();

        return $this->onRefresh();
    }

    public function onRefresh()
    {
        return [
            '#'.$this->getId('asset-list') => $this->makePartial('items', ['items'=>$this->getData()])
        ];
    }
    protected function setSearchTerm($term)
    {
        $this->searchTerm = trim($term);
        $this->putSession('search', $this->searchTerm);
    }

    protected function extendSelection()
    {
        $items = Input::get('file', []);
        $currentSelection = $this->getSelectedFiles();

        $this->putSession($this->getThemeSessionKey('selected'), array_merge($currentSelection, $items));
    }

    protected function getSelectedFiles()
    {
        if ($this->selectedFilesCache !== false) {
            return $this->selectedFilesCache;
        }

        $files = $this->getSession($this->getThemeSessionKey('selected'), []);
        if (!is_array($files)) {
            return $this->selectedFilesCache = [];
        }

        return $this->selectedFilesCache = $files;
    }

    protected function getThemeSessionKey($prefix)
    {
        return $prefix.$this->plugin->getDirName();
    }

    protected function isFileSelected($item)
    {
        $selectedFiles = $this->getSelectedFiles();
        if (!is_array($selectedFiles) || !isset($selectedFiles[$item->path])) {
            return false;
        }

        return $selectedFiles[$item->path];
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

//    protected function getRenderData()
//    {
//        $activePluginVector = $this->controller->getBuilderActivePluginVector();
//
//        return [
//            'pluginVector'=>$activePluginVector
//        ];
//return;
//        $items = $this->getControllerList($activePluginVector->pluginCodeObj);
//
//        $searchTerm = Str::lower($this->getSearchTerm());
//        if (strlen($searchTerm)) {
//            $words = explode(' ', $searchTerm);
//            $result = [];
//
//            foreach ($items as $controller) {
//                if ($this->textMatchesSearch($words, $controller)) {
//                    $result[] = $controller;
//                }
//            }
//
//            $items = $result;
//        }
//
//        return [
//            'pluginVector'=>$activePluginVector,
//            'items'=>$items
//        ];
//    }
//
//
//    public function refreshActivePlugin()
//    {
//        return ['#'.$this->getId('body') => $this->makePartial('widget-contents', $this->getRenderData())];
//    }

    public function onAddItem()
    {
        $vars =  post('New');
        $this->readyVars = $this->processVars($vars);

        $this->newPluginData = $vars;
        $this->makeFiles();
        $this->modifyFiles();
        $this->octoberUp();

        Flash::success("Successfully created {$this->readyVars['author']}.{$this->readyVars['plugin']} plugin");
    }

    private function makeFiles()
    {
        $old_umask = umask(0);//we will change the umask to make the respective directories and files 777
        //so that users can edit them after they are created, otherwise only the apache group will be able to edit them
        $this->createPlugin();
        $this->createComponent();
        $this->createController();
        $this->createModel();
        umask($old_umask);//return to the original mask
    }

    private function createPlugin()
    {
        $input= $this->newPluginData;
        $pluginName = $input['plugin'];
        $authorName = $input['author'];
        $vars = [
            'name'   => $pluginName,
            'author' => $authorName,
        ];
        $destinationPath = base_path() . '/plugins';
        Plugin::make($destinationPath, $vars);
    }

    private function createComponent()
    {
        $input= $this->newPluginData;
        $pluginName = $input['plugin'];
        $authorName = $input['author'];

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $componentName = $input['component'];
        $modelName = $input['model'];
        //we will send the model name so that we can add it to the instructor's view-- it will be needed
        $vars = [
            'name' => $componentName,
            'author' => $authorName,
            'plugin' => $pluginName,
            'model'=> $modelName
        ];

        Component::make($destinationPath, $vars);
    }

    private function createController()
    {
        $input= $this->newPluginData;
        $pluginName = $input['plugin'];
        $authorName = $input['author'];

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $controllerName = $input['controller'];

        /*
         * Determine the model name to use,
         * either supplied or singular from the controller name.
         */
        $modelName = $input['model'];
        if (!$modelName)
            $modelName = Str::singular($controllerName);

        $vars = [
            'name' => $controllerName,
            'model' => $modelName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Controller::make($destinationPath, $vars);
    }

    private function createModel()
    {
        $input= $this->newPluginData;
        $pluginName = $input['plugin'];
        $authorName = $input['author'];

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $modelName = $input['model'];
        $vars = [
            'name' => $modelName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Model::make($destinationPath, $vars);
    }


    private function modifyFiles()
    {//the model doesn't need to be modified
        $this->modifyController();
        $this->modifyComponent();
        $this->modifyPlugin();
        $this->modifyVersion();

    }

    private function modifyController()
    {
        $readyVars = $this->readyVars;
        //path to model
        $destinationPath = base_path() . '/plugins/' .$readyVars['lower_author'] . '/' . $readyVars['lower_plugin']."/controllers/".$readyVars['studly_controller'].".php";
        $modelUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Models\\".$readyVars['studly_model'];
        $controllerNodevisitor = new ControllerNodeVisitor($modelUseStmt, "MyModel");

        $this->openModifySave($destinationPath, $controllerNodevisitor);
    }

    private function modifyComponent()
    {
        $readyVars = $this->readyVars;
        //path to model
        $destinationPath = base_path() . '/plugins/' . $readyVars['lower_author'] . '/' .$readyVars['lower_plugin']."/components/".$readyVars['studly_component'].".php";
        $modelUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Models\\".$readyVars['studly_model'];
        $controllerUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Controllers\\".$readyVars['studly_controller'];
        $componentNodevisitor = new ComponentNodeVisitor($modelUseStmt, "MyModel", $controllerUseStmt, "MyController", $readyVars['studly_model']);

        $this->openModifySave($destinationPath, $componentNodevisitor);
    }

    private function modifyPlugin()
    {
        $readyVars = $this->readyVars;
        //path to model
        $destinationPath = base_path() . '/plugins/' . $readyVars['lower_author'] . '/' .$readyVars['lower_plugin']."/Plugin.php";

        $componentPath = '\\'.$readyVars['studly_author'] . '\\' .$readyVars['studly_plugin']."\\Components\\".$readyVars['studly_component'];
        $controllerPath = $readyVars['lower_author'] . '/' . $readyVars['lower_plugin'].'/'.$readyVars['lower_controller'];

        $pluginNodeVisitor = new PluginNodeVisitor($componentPath,$readyVars['lower_component'],$controllerPath, $readyVars['studly_controller'],
            $readyVars['lower_plugin'],$readyVars['lower_author']
        );
        $this->openModifySave($destinationPath, $pluginNodeVisitor);
    }

    private function modifyVersion()
    {
        $readyVars = $this->readyVars;
        $yamlDestinationPath = base_path() . '/plugins/' . $readyVars['lower_author'] . '/' .$readyVars['lower_plugin']."/updates/version.yaml";
        $yaml = new Parser();
        $current = $yaml->parse(file_get_contents($yamlDestinationPath));
        end($current);         // move the internal pointer to the end of the array
        $key = key($current);  // fetches the key of the element pointed to by the internal pointer
        $arr = array_map('intval', explode('.', $key));
        $right = array_pop($arr);
        $arr[] = ++$right;
        $newVersion = implode(".", $arr);

        $newItemToAdd = ["create {$readyVars['snake_plural_model']} table","create_{$readyVars['snake_plural_model']}_table.php"];
        $current[$newVersion]=$newItemToAdd;
        $dumper = new Dumper();
        $yaml = $dumper->dump($current, 2);
        file_put_contents($yamlDestinationPath, $yaml);
    }

    private function octoberUp()
    {
        $pluginManager = PluginManager::instance();
        $pluginManager->loadPlugins();//loads the newly created plugin
        $manager = UpdateManager::instance()->resetNotes()->update();//updates october's plugins
        return;
    }

    private function openModifySave($fileDestination, $nodeVisitor)
    {
        $fileSystem = new Filesystem;
        $fileContent = $fileSystem->get($fileDestination);
        $parser = new \PhpParser\ParserFactory();
        $newParser = $parser->create(ParserFactory::PREFER_PHP5);
        $prettyPrinter = new PrettyPrinter\Standard;
        $traverser = new NodeTraverser;
        $traverser->addVisitor($nodeVisitor);

        try {
            //parse the PHP class
            $stmts = $newParser->parse($fileContent);
            //traverse the nodes and make the necessary modifications
            $stmts = $traverser->traverse($stmts);

            // pretty print back to code
            $code = $prettyPrinter->prettyPrintFile($stmts);
            //save the file back
            $fileSystem->put($fileDestination, $code);

        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
    }

    /**
     * Converts all variables to available modifier and case formats.
     * Syntax is CASE_MODIFIER_KEY, eg: lower_plural_xxx
     *
     * @param array The collection of original variables
     * @return array A collection of variables with modifiers added
     */
    protected function processVars($vars)
    {
        $cases = ['upper', 'lower', 'snake', 'studly', 'camel', 'title'];
        $modifiers = ['plural', 'singular', 'title'];

        foreach ($vars as $key => $var) {

            /*
             * Apply cases, and cases with modifiers
             */
            foreach ($cases as $case) {
                $primaryKey = $case . '_' . $key;
                $vars[$primaryKey] = $this->modifyString($case, $var);

                foreach ($modifiers as $modifier) {
                    $secondaryKey = $case . '_' . $modifier . '_' . $key;
                    $vars[$secondaryKey] = $this->modifyString([$modifier, $case], $var);
                }
            }

            /*
             * Apply modifiers
             */
            foreach ($modifiers as $modifier) {
                $primaryKey = $modifier . '_' . $key;
                $vars[$primaryKey] = $this->modifyString($modifier, $var);
            }

        }
        return $vars;
    }

    /**
     * Internal helper that handles modify a string, with extra logic.
     * @param string|array $type
     * @param string $string
     * @return string
     */
    protected function modifyString($type, $string)
    {
        if (is_array($type)) {
            foreach ($type as $_type) {
                $string = $this->modifyString($_type, $string);
            }

            return $string;
        }

        if ($type == 'title') {
            $string = str_replace('_', ' ', Str::snake($string));
        }

        return Str::$type($string);
    }

    protected function getGroupStatus($group)
    {
        $statuses = $this->getGroupStatuses();
        if (array_key_exists($group, $statuses)) {
            return $statuses[$group];
        }

        return false;
    }

    protected function getGroupStatuses()
    {
        echo "here";
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

}
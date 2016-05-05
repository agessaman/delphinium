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
use Yaml;
use Flash;


class Delphiniumize extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    protected $theme;
    protected $controller;
    public $noRecordsMessage = 'rainlab.builder::lang.database.no_records';

    protected $newPluginData;
    protected $readyVars;
    protected $fileVersions;
    protected $pluginManager;

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
        return $this->makePartial('delphiniumize');
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
}
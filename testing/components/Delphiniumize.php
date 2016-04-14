<?php namespace Delphinium\Testing\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Support\Str;
use Delphinium\Greenhouse\Templates\Component;
use Delphinium\Greenhouse\Templates\Plugin;
use Delphinium\Greenhouse\Templates\Controller;
use Delphinium\Greenhouse\Templates\Model;
use Delphinium\Testing\Classes\PluginNodeVisitor;
use Delphinium\Testing\Classes\ControllerNodeVisitor;
use Delphinium\Testing\Classes\ComponentNodeVisitor;
use October\Rain\Filesystem\Filesystem;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use System\Classes\VersionManager;
use System\Classes\PluginManager;
use Yaml;

class Delphiniumize extends ComponentBase
{

    protected $newPluginData;
    protected $readyVars;
    protected $fileVersions;
    protected $pluginManager;
    public function componentDetails()
    {
        return [
            'name'        => 'Delphiniumize Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $vars =  array("author"=>"author","plugin"=>"newPlugin", "component"=>"newComponent", "controller"=>"newController", "model"=>"newModel");

        $this->readyVars = $this->processVars($vars);
        $this->newPluginData = $vars;
        $this->makeFiles();
        $this->modifyFiles();
    }
    public function onAddItem()
    {
        $vars =  post('New');
        $this->readyVars = $this->processVars($vars);

        $this->newPluginData = $vars;
        $this->makeFiles();
        $this->modifyFiles();
    }

    private function makeFiles()
    {
        $this->createPlugin();
        $this->createComponent();
        $this->createController();
        $this->createModel();
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

        $vars = [
            'name' => $componentName,
            'author' => $authorName,
            'plugin' => $pluginName
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
        $destinationPath = base_path() . '/plugins/' .$readyVars['studly_author'] . '/' . $readyVars['studly_plugin']."/controllers/".$readyVars['studly_controller'].".php";
        $modelUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Models\\".$readyVars['studly_model'];
        $controllerNodevisitor = new ControllerNodeVisitor($modelUseStmt, "MyModel");

        $this->openModifySave($destinationPath, $controllerNodevisitor);
    }

    private function modifyComponent()
    {
        $readyVars = $this->readyVars;
        //path to model
        $destinationPath = base_path() . '/plugins/' . $readyVars['studly_author'] . '/' .$readyVars['studly_plugin']."/Components/".$readyVars['studly_component'].".php";
        $modelUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Models\\".$readyVars['studly_model'];
        $controllerUseStmt = $readyVars['studly_author'] . '\\' . $readyVars['studly_plugin']."\\Controllers\\".$readyVars['studly_controller'];
        $componentNodevisitor = new ComponentNodeVisitor($modelUseStmt, "MyModel", $controllerUseStmt, "MyController", $readyVars['studly_model']);

        $this->openModifySave($destinationPath, $componentNodevisitor);
    }

    private function modifyPlugin()
    {
        $readyVars = $this->readyVars;
        //path to model
        $destinationPath = base_path() . '/plugins/' . $readyVars['studly_author'] . '/' .$readyVars['studly_plugin']."/Plugin.php";

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
        $yamlDestinationPath = base_path() . '/plugins/' . $readyVars['studly_author'] . '/' .$readyVars['studly_plugin']."/updates/version.yaml";
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
    private function openModifySave($fileDestination, $nodeVisitor)
    {
        $fileSystem = new Filesystem;
        $fileContent = $fileSystem->get($fileDestination);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $prettyPrinter = new PrettyPrinter\Standard;
        $traverser = new NodeTraverser;
        $traverser->addVisitor($nodeVisitor);

        try {
            //parse the PHP class
            $stmts = $parser->parse($fileContent);
            //traverse the nodes and make the necessary modifications
            $stmts = $traverser->traverse($stmts);

//            var_dump($stmts);
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
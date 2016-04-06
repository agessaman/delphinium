<?php namespace Delphinium\Testing\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Support\Str;
use Delphinium\Greenhouse\Templates\Component;
use Delphinium\Greenhouse\Templates\Plugin;
use Delphinium\Greenhouse\Templates\Controller;
use Delphinium\Greenhouse\Templates\Model;
use Delphinium\Testing\Classes\MyNodeVisitor;
use October\Rain\Filesystem\Filesystem;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;

class MyComponent extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'MyComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        //$this->randomFunc();

        ini_set('xdebug.var_display_max_depth', 5);
        ini_set('xdebug.var_display_max_children', 256);
        ini_set('xdebug.var_display_max_data', 2048);

        $fileSystem = new Filesystem;
//        $destinationContent = "My text";
        $fileDestination = "C:/wamp/www/delphinium/plugins/delphinium/uliop/Plugin.php";
        $fileContent = $fileSystem->get($fileDestination);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $prettyPrinter = new PrettyPrinter\Standard;
        $traverser     = new NodeTraverser;
        $traverser->addVisitor(new MyNodeVisitor);

        try {
            $stmts = $parser->parse($fileContent);
            $stmts = $traverser->traverse($stmts);

            // pretty print
            $code = $prettyPrinter->prettyPrintFile($stmts);
            $fileSystem->put($fileDestination, $code);
            //var_dump($code);

            //TO BUILD NODES USE THE BUILDERFACTORY
//            $factory = new BuilderFactory;
//            $factory->namespace("a")->addStmt()
            //PhpParser\Node\Scalar

        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
    }


    private function randomFunc()
    {

        $fileSystem = new Filesystem;
//        $destinationContent = "My text";
        $fileDestination = "C:/wamp/www/delphinium/plugins/delphinium/uliop/Plugin.php";
        $this->registerComponentInPlugin("Delphinium.Uliop", "NewComp", $fileDestination);
    }

    private function registerComponentInPlugin($pluginCode, $componentName, $fileDestination)
    {
        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);

//        $search = '//KEY-registerComponent'."\r\n".'return ['; // the content after which you want to insert new stuff
        $search = "//KEY-registerComponent return [";
        echo($search);
        $insert = "{$authorName}\{$pluginName}\Components\{$componentName}\' => \'{$componentName}\',";
        $this->insertContent($fileDestination,$search, $insert);
    }
    private function insertContent($fileDestination, $searchString, $insertString)
    {
        $fileSystem = new Filesystem;
        $fileContent = $fileSystem->get($fileDestination);

        ini_set('xdebug.var_display_max_depth', 5);
        ini_set('xdebug.var_display_max_children', 256);
        ini_set('xdebug.var_display_max_data', 1024);
        echo($fileContent);
        $replace = $searchString . "\n" . $insertString."\n";
        $newFileContent = str_replace($searchString, $replace, $fileContent);
        $fileSystem->put($fileDestination, $newFileContent);
    }
    private function makeFiles()
    {
        $vars =  array("pluginCode"=>"Delphinium.Uliop", "componentName"=>"NewComp", "controllerName"=>"NewControl", "modelName"=>"NewModel");
        $this->createPlugin($vars);
//        $this->createComponent($vars);
//        $this->createController($vars);
//        $this->createModel($vars);
    }

    private function modifyFiles()
    {

    }
    private function createPlugin($input)
    {
        $pluginCode = $input['pluginCode'];
        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);
        $vars = [
            'name'   => $pluginName,
            'author' => $authorName,
        ];
        $destinationPath = base_path() . '/plugins';
        Plugin::make($destinationPath, $vars);
    }

    private function createComponent($input)
    {
        $pluginCode = $input['pluginCode'];
        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $componentName = $input['componentName'];

        $vars = [
            'name' => $componentName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Component::make($destinationPath, $vars);
    }

    private function createController($input)
    {
        $pluginCode = $input['pluginCode'];
        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $controllerName = $input['controllerName'];

        /*
         * Determine the model name to use,
         * either supplied or singular from the controller name.
         */
        $modelName = Str::singular($controllerName);

        $vars = [
            'name' => $controllerName,
            'model' => $modelName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Controller::make($destinationPath, $vars);
    }

    private function createModel($input)
    {
        $pluginCode = $input['pluginCode'];
        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $modelName = $input['modelName'];
        $vars = [
            'name' => $modelName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Model::make($destinationPath, $vars);
    }




}
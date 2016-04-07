<?php namespace Delphinium\Testing\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Support\Str;
use Delphinium\Greenhouse\Templates\Component;
use Delphinium\Greenhouse\Templates\Plugin;
use Delphinium\Greenhouse\Templates\Controller;
use Delphinium\Greenhouse\Templates\Model;
use Delphinium\Testing\Classes\PluginFileNodeVisitor;
use October\Rain\Filesystem\Filesystem;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;

class Delphiniumize extends ComponentBase
{

    protected $newPluginData;
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

    public function onAddItem()
    {
        $vars =  post('New');
        $this->newPluginData = $vars;
//        $this->makeFiles();
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
    {
        $this->modifyModel();
        $this->modifyController();
        $this->modifyComponent();
        $this->modifyPlugin();

    }

    private function modifyModel()
    {
        $authorName = $this->newPluginData['author'];
        $pluginName = $this->newPluginData['plugin'];
        $modelName = $this->newPluginData['model'];
        //path to model
        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName)."/models/".$modelName.".php";
return;
        $fileSystem = new Filesystem;
        $fileDestination = "C:/wamp/www/delphinium/plugins/delphinium/uliop/Plugin.php";
        $fileContent = $fileSystem->get($fileDestination);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $prettyPrinter = new PrettyPrinter\Standard;
        $traverser     = new NodeTraverser;
        $traverser->addVisitor(new PluginFileNodeVisitor);

        try {
            $stmts = $parser->parse($fileContent);
            $stmts = $traverser->traverse($stmts);
            // pretty print
            $code = $prettyPrinter->prettyPrintFile($stmts);
            echo "-------------";
            var_dump($code);
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

    private function modifyController()
    {

    }

    private function modifyComponent()
    {

    }

    private function modifyPlugin()
    {

    }

}
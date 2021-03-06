<?php namespace Delphinium\Vanilla\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use Delphinium\Vanilla\Classes\PluginBaseModel;
use PhpParser\ParserFactory;
use PhpParser\Error;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\BuilderFactory;
use Yaml;
use ApplicationException;
use Input;
use Config;

/**
 * Plugin management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexPluginOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/delphinium/vanilla/classes/pluginbasemodel/fields.yaml';
    protected $pluginData;

    public function onPluginLoadPopup()
    {
        $pluginCode = Input::get('pluginCode');
        try {
            $this->vars['form'] = $this->makeBaseFormWidget($pluginCode);
            $this->vars['pluginCode'] = $pluginCode;
        }
        catch (ApplicationException $ex) {
            $this->vars['errorMessage'] = $ex->getMessage();
        }

        return $this->makePartial('plugin-popup-form');
    }

    public function onPluginSave()
    {
        $pluginCode = Input::get('pluginCode');

        $model = $this->loadOrCreateBaseModel($pluginCode);
        $model->fill($_POST);
        $model->save();

        if (!$pluginCode) {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode(),
                'isNewPlugin' => 1
            ];

            return $result;
        } else {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode()
            ];

            return array_merge($result, $this->controller->updatePluginList()); 
        }
    }

    public function onPluginSetActive()
    {
        $pluginCode = Input::get('pluginCode');
        $updatePluginList = Input::get('updatePluginList');
        $result = $this->controller->setBuilderActivePlugin($pluginCode, false);
        if ($updatePluginList) {
            $result = array_merge($result, $this->controller->updatePluginList());
        }

        $result['responseData'] = ['pluginCode'=>$pluginCode];

        return $result;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new PluginBaseModel();

        if (!$pluginCode) {
            $model->initDefaults();
            return $model;
        }

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
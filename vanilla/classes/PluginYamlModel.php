<?php namespace Delphinium\Vanilla\Classes;

use ApplicationException;
use Lang;
use File;
use Rainlab\Builder\Classes\YamlModel;
use Rainlab\Builder\Classes\PluginCode;

/**
 * A base class for models that keep data in the plugin.yaml file.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class PluginYamlModel extends YamlModel
{
    protected $pluginName;

    public function loadPlugin($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $filePath = self::pluginSettingsFileExists($pluginCodeObj);
        if ($filePath === false) {
            throw new ApplicationException("Settings of this plugin cannot be edited with Vanilla");
        }

        $this->initPropertiesFromPluginCodeObject($pluginCodeObj);

        $result = parent::load($filePath);

        $this->loadCommonProperties();

        return $result;
    }

    public function getPluginName()
    {
        return Lang::get($this->pluginName);
    }

    protected function loadCommonProperties()
    {
        if (!array_key_exists('plugin', $this->originalFileData)) {
            return;
        }

        $pluginData = $this->originalFileData['plugin'];

        if (array_key_exists('name', $pluginData)) {
            $this->pluginName = $pluginData['name'];
        }
    }

    protected function initPropertiesFromPluginCodeObject($pluginCodeObj)
    {
    }

    protected static function pluginSettingsFileExists($pluginCodeObj)
    {
        $filePath = File::symbolizePath($pluginCodeObj->toPluginFilePath());
        if (File::isFile($filePath)) {
            return $filePath;
        }

        return false;
    }
}
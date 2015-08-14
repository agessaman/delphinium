<?php namespace Delphinium\Xylum;

use System\Classes\PluginBase;

/**
 * Xylum Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Xylum',
            'description' => 'Rule Manager',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Delphinium\Xylum\Components\Manager' => 'manager'
        ];
    }
}

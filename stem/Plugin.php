<?php namespace Delphinium\Stem;

use System\Classes\PluginBase;

/**
 * Stem Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Delphinium.Greenhouse',
        'Delphinium.Dev',
        'Delphinium.Core'
    ];
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Stem',
            'description' => 'Module Manager',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Delphinium\Stem\Components\Manager' => 'stem'
        ];
    }
    
}

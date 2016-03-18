<?php namespace Delphinium\BirdoParadise;

use System\Classes\PluginBase;
use Backend;
use Event;

/**
 * BirdoParadise Plugin Information File
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
            'name'        => 'Bird of Paradise',
            'description' => 'Mapped modules maker',
            'author'      => 'Delphinium',
            'icon'        => 'icon-binoculars'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Delphinium\BirdoParadise\Components\Modulemap' => 'modulemap',
        ];
    }

}

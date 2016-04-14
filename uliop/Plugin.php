<?php

namespace Delphinium\Uliop;

use Backend;
use System\Classes\PluginBase;
use Event;
/**
 * Uliop Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = array('Delphinium.Greenhouse', 'Delphinium.Dev', 'Delphinium.Roots');
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return array('name' => 'Uliop', 'description' => 'No description provided yet...', 'author' => 'Delphinium', 'icon' => 'icon-leaf');
    }
    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        /*Do NOT remove these comments!!! They are used to automate the "Delphiniumization" of your plugin!"*/
        //KEY-registerComponent
        return array('\\Delphinium\\Uliop\\Components\\NewComp' => 'newcomp', '\\Delphinium\\Uliop\\Components\\AnotherComp' => 'anothercomp', '\\Delphinium\\Uliop\\Components\\CreatedComp' => 'createdcomp');
    }
    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return array();
        // Remove this line to activate
        return array('delphinium.uliop.some_permission' => array('tab' => 'Uliop', 'label' => 'Some permission'));
    }
    /**
     * Adds a navigation item for this plugin's controllers in Delphinium's Greenhouse backend
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('backend.menu.extendItems', function ($manager) {
        });
    }
}
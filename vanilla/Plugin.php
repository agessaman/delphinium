<?php namespace Delphinium\Vanilla;

use System\Classes\PluginBase;
use Backend;
Use Event;


/**
 * Vanilla Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Delphinium.Greenhouse',
        'Delphinium.Dev',
        'Delphinium.Roots'
    ];
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Vanilla',
            'description' => 'Vanilla plugin to demonstrate how to create and apply rules',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }
    public function registerComponents()
    {
        return [
            '\Delphinium\Vanilla\Components\Bonus' => 'bonus'
        ];
    }
    
    public function boot()
    {
	    
    	Event::listen('backend.menu.extendItems', function($manager){	
    	 
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Bonus' => [
                    'label' => 'Bonus',
                    'icon' => 'icon-bullseye',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/vanilla/bonus'),
                    'group'       => 'Vanilla',
                ]
            ]);
            
        });
    }
}

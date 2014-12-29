<?php namespace Delphinium\Raspberry;

use Backend;
Use Event;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
	public $require = [
    	'Delphinium.Greenhouse',
    	'Delphinium.Blackberry'
	];

    public function pluginDetails()
    {
        return [
            'name' => 'Raspberry',
            'description' => 'Provides API helper to interact with Canvas\' API',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-cubes'
        ];
    }

    
    public function boot()
    {
    	
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Raspberry' => [
					'label' => 'Raspberry',
					'icon' => 'icon-cubes',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/raspberry/api'),
				]
            ]);
            
        });
    }
}
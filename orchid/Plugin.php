<?php namespace Delphinium\Orchid;

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
            'name' => 'Orchid',
            'description' => 'Displays a leader board of a Canvas course',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-ellipsis-v'
        ];
    }

    
    public function boot()
    {
    	
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Orchid' => [
					'label' => 'Orchid',
					'icon' => 'icon-ellipsis-v',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/orchid/board'),
                    'group'       => 'Orchid',
				]
            ]);
            
        });
    }
}
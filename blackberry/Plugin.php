<?php namespace Delphinium\Blackberry;

use Backend;
Use Event;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
	public $require = [
            'Delphinium.Greenhouse',
            'Delphinium.Roots'
	];

    public function pluginDetails()
    {
        return [
            'name' => 'Blackberry',
            'description' => 'Performs LTI handshake.',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-cogs'
        ];
    }

    
    public function registerComponents()
    {
        return [
            '\Delphinium\Blackberry\Components\LTIConfiguration' => 'LTIConfiguration'
        ];
    }
    
    public function boot()
    {
    
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Blackberry' => [
					'label' => 'Blackberry',
					'icon' => 'icon-cogs',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/blackberry/configuration'),
				]
            ]);
            
        });
        
        
    }
   
   
}
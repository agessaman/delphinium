<?php namespace Delphinium\Roots;

use System\Classes\PluginBase;
Use Event;
use Backend;

class Plugin extends PluginBase
{

    public $require = [
        'Delphinium.Greenhouse',
        'Delphinium.Dev'
    ];

    public function pluginDetails()
    {
        return [
            'name' => 'Roots',
            'description' => 'Data Abstrction Layer of Delphinium',
            'author' => 'Delphinium',
            'icon' => 'icon-cubes'
        ];
    }
    
    public function registerComponents()
    {
        return [
            '\Delphinium\Roots\Components\LTIConfiguration' => 'LTIConfiguration'
        ];
    }
    
    public function boot()
    {
    
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'LTIconfig' => [
					'label' => 'LTI Configuration',
					'icon' => 'icon-cogs',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/roots/lticonfiguration'),
				]
            ]);
            
        });
        
        
    }

}

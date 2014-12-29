<?php namespace Delphinium\Greenhouse;

use Backend;
Use Event;
use System\Classes\PluginBase;
use October\Rain\Support\Traits\Emitter;
use BackendMenu;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Greenhouse Plugin',
            'description' => 'Provides gamification widgets for learning suites',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-leaf'
        ];
    }

    
    public function boot()
    {   
    	//here we fire the event so all other plugins can register
    	Event::fire('delphinium.greenhouse.load');
    	 
    }
    
    
    public function registerNavigation()
	{
    	return [
            'greenhouse' => [
                'label'       => 'Greenhouse',
                'url'         => Backend::url('delphinium/greenhouse/greenhouse'),
                'icon'        => 'icon-leaf',
                'order'       => 500,
            ]
        ];
	}
	
}
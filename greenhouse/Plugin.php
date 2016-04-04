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

    public function register()
    {
        BackendMenu::registerContextSidenavPartial('Delphinium.Greenhouse', 'greenhouse', '@/plugins/delphinium/greenhouse/partials/_sidebar.htm');

        //register console commands
        $this->registerConsoleCommand('Delphinium.DelphiniumPlugin', 'Delphinium\Greenhouse\Console\DelphiniumPlugin');
        $this->registerConsoleCommand('Delphinium.DelphiniumComponent', 'Delphinium\Greenhouse\Console\DelphiniumComponent');
        $this->registerConsoleCommand('Delphinium.DelphiniumController', 'Delphinium\Greenhouse\Console\DelphiniumController');
        $this->registerConsoleCommand('Delphinium.DelphiniumModel', 'Delphinium\Greenhouse\Console\DelphiniumModel');
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
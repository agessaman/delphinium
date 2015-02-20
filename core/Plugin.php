<?php namespace Delphinium\Core;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
	public $require = [
    	'Delphinium.Greenhouse'
	];

    public function pluginDetails()
    {
        return [
            'name' => 'Core',
            'description' => 'Data Abstrction Layer of Delphinium',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-cubes'
        ];
    }

    
    public function boot()
    {
    	
    	
    }
}
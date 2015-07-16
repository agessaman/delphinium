<?php namespace Delphinium\Roots;

use System\Classes\PluginBase;

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

}

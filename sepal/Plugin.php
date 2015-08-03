<?php namespace Delphinium\Sepal;

use System\Classes\PluginBase;

/**
 * Sepal Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Sepal',
            'description' => 'Rule Aggregation Manager',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }
    
    public function registerComponents()
    {
        return [
            '\Delphinium\Sepal\Components\Ruler' => 'ruler'
        ];
    }

}

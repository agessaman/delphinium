<?php namespace Delphinium\Xylum;

use System\Classes\PluginBase;

/**
 * Xylum Plugin Information File
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
            'name'        => 'Xylum',
            'description' => 'No description provided yet...',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

}

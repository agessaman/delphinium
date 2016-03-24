<?php namespace Delphinium\Threads;

use Backend;
use System\Classes\PluginBase;

/**
 * Threads Plugin Information File
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
            'name'        => 'Threads',
            'description' => 'No description provided yet...',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Delphinium\Threads\Components\Test' => 'test',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'delphinium.threads.some_permission' => [
                'tab' => 'Threads',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'threads' => [
                'label'       => 'Threads',
                'url'         => Backend::url('delphinium/threads/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.threads.*'],
                'order'       => 500,
            ],
        ];
    }

}

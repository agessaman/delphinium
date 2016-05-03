<?php namespace Delphinium\Vanilla;

use Backend;
use System\Classes\PluginBase;

/**
 * Vanilla Plugin Information File
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
            'name'        => 'Vanilla',
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
        return []; // Remove this line to activate

        return [
            'Delphinium\Vanilla\Components\MyComponent' => 'myComponent',
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
            'delphinium.vanilla.some_permission' => [
                'tab' => 'Vanilla',
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
        return [
            'vanilla' => [
                'label'       => 'Vanilla',
                'url'         => \Backend::url('delphinium/vanilla/delphiniumize'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.vanilla.*'],
                'order'       => 500,
            ],
        ];
    }

    public function register()
    {
        \BackendMenu::registerContextSidenavPartial('Delphinium.Vanilla', 'vanilla', '@/plugins/delphinium/vanilla/partials/_sidebar.htm');
    }
}


<?php namespace Delphinium\Vanilla;

use Event;
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
        return [
            'Delphinium\Vanilla\Components\Vanilla' => 'vanilla',
        ];
    }

	public function boot()
	{
	  Event::listen('backend.menu.extendItems', function($manager) {
		$manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
			'Vanilla' => [
			  'label' => 'Vanilla',
			  'icon'  => 'icon-bar-chart',
			  'owner' => 'Delphinium.Greenhouse',
			  'url' => Backend::url('delphinium/vanilla/vanilla')
			]
		  ]);
	  });
	}

    /** UNUSED
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
        return []; // Remove this line to activate

        return [
            'vanilla' => [
                'label'       => 'Vanilla',
                'url'         => Backend::url('delphinium/vanilla/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.vanilla.*'],
                'order'       => 500,
            ],
        ];
    }

}

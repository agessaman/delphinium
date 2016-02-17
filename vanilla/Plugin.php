<?php namespace Delphinium\Vanilla;

use System\Classes\PluginBase;
use Backend;
use Event;

/**
 * vanilla Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'vanilla',
            'description' => 'Vanilla Plugin',
            'author'      => 'Travis Jones',
            'icon'        => 'icon-thumbs-up'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     * @return array
     */
    public function registerComponents()
    {
        return [
            '\Delphinium\Vanilla\Components\Vanilla' => 'vanilla'
        ];
    }
	public function boot()
	{
	  Event::listen('backend.menu.extendItems', function($manager) {
		$manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
			'Vanilla' => [
			  'label' => 'Vanilla',
			  'icon'  => 'icon-thumbs-up',
			  'owner' => 'Delphinium.Greenhouse',
			  'url' => Backend::url('delphinium/vanilla/board')
			]
		  ]);
	  });
	}
    /**
	 These additional functions, registerPermissions() and registerNavigation(). 
	 You can delete the registerPermissions() functions because all our components are meant to be used inside Canvas, and we will take care of permissions that way. 
	 The registerNavigation() function can also be deleted because we don't want to add a navigation item at the top of delphinium for each plugin. 
	 Rather, we want to add them to Greenhouse (with the bit of code above, extendItems.
     * Registers any back-end permissions used by this plugin.
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'delphinium.vanilla.some_permission' => [
                'tab' => 'vanilla',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'vanilla' => [
                'label'       => 'vanilla',
                'url'         => Backend::url('delphinium/vanilla/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.vanilla.*'],
                'order'       => 500,
            ],
        ];
    }

}

<?php namespace Delphinium\Poppies;

use Event;
use Backend;
use System\Classes\PluginBase;

/**
 * Poppies Plugin Information File
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
            'name'        => 'Poppies',
            'description' => 'No description provided yet...',
            'author'      => 'Delphinium',
            'icon'        => 'icon-thumbs-o-up'
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
			'\Delphinium\Poppies\Components\Popquiz' => 'popquiz'
		];
    }
    
    /**
     * Add component to Greenhouse
     */
    public function boot()
    {
      Event::listen('backend.menu.extendItems', function($manager) {
        $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
            'Poppies' => [
              'label' => 'Poppies',
              'icon'  => 'icon-thumbs-o-up',
              'owner' => 'Delphinium.Greenhouse',
			  'group' => 'Orchid',
              'url' => Backend::url('delphinium/poppies/popquiz')
            ]
          ]);// poppies controller
      });
    }
    
/************** unused ********************/
    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'delphinium.poppies.some_permission' => [
                'tab' => 'Poppies',
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
            'poppies' => [
                'label'       => 'Poppies',
                'url'         => Backend::url('delphinium/poppies/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.poppies.*'],
                'order'       => 500,
            ],
        ];
    }

}

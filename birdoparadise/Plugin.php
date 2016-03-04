<?php namespace Delphinium\BirdoParadise;

use System\Classes\PluginBase;
use Backend;
use Event;

/**
 * BirdoParadise Plugin Information File
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
            'name'        => 'Bird of Paradise',
            'description' => 'Mapped modules maker',
            'author'      => 'Delphinium',
            'icon'        => 'icon-binoculars'
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
            'Delphinium\BirdoParadise\Components\Modulemap' => 'modulemap',
        ];
    }
    
    /**
     * Add component to Greenhouse
     */
    public function boot()
    {
      Event::listen('backend.menu.extendItems', function($manager) {
        $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
            'Modulemap' => [
              'label' => 'Module Map',
              'icon'  => 'icon-binoculars',
              'owner' => 'Delphinium.Greenhouse',
              'url' => Backend::url('delphinium/birdoparadise/modulemap')
            ]
          ]);
      });
    }

/****************** unused *********************/
    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'delphinium.birdoparadise.some_permission' => [
                'tab' => 'BirdoParadise',
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
            'birdoparadise' => [
                'label'       => 'BirdoParadise',
                'url'         => Backend::url('delphinium/birdoparadise/modulemap'),
                'icon'        => 'icon-leaf',
                'permissions' => ['delphinium.birdoparadise.*'],
                'order'       => 500,
            ],
        ];
    }

}

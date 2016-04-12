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
            'description' => 'Quiz Games',
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
			'Delphinium\Poppies\Components\Popquiz' => 'popquiz'
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
          ]);
      });
    }

}

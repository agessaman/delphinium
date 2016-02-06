<?php namespace Delphinium\Dev;

use Backend;
Use Event;
use System\Classes\PluginBase;

/**
 * Dev Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Delphinium.Greenhouse'
    ];
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Dev',
            'description' => 'This plugin will be used to configure development mode',
            'author'      => 'Delphinium',
            'icon'        => 'icon-leaf'
        ];
    }

    public function boot()
    {

        Event::listen('backend.menu.extendItems', function($manager){

            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Dev' => [
                    'label' => 'Dev',
                    'icon' => 'icon-cogs',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/dev/configuration'),
                ]
            ]);

        });
    }

    public function registerComponents()
    {
        return [
            '\Delphinium\Dev\Components\Dev' => 'dev',
            '\Delphinium\Dev\Components\TestRoots' => 'testRoots',
            '\Delphinium\Dev\Components\Data' => 'data'
        ];
    }

}

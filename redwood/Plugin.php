<?php namespace Delphinium\Redwood;

use Backend;
use System\Classes\PluginBase;
Use Event;

/**
 * Redwood Plugin Information File
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
            'name'        => 'Redwood',
            'description' => 'Interface between Canvas and ProcessMaker',
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
            'Delphinium\Redwood\Components\Oauth' => 'Oauth',
            'Delphinium\Redwood\Components\TestRedwoodRoots' => 'testRedwoodRoots',
            'Delphinium\Redwood\Components\PeerReview' => 'PeerReview'
        ];
    }


    public function boot()
    {
        Event::listen('backend.menu.extendItems', function($manager) {

            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'OAuth' => [
                    'label' => 'Oauth',
                    'icon' => 'icon-key',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/redwood/oauth'),
                    'group'       => 'Redwood',
                ]
            ]);

        });
    }
}

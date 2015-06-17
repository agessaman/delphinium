<?php namespace Delphinium\Blossom;

use System\Classes\PluginBase;
use Backend;
Use Event;

/**
 * Blossom Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Delphinium.Greenhouse',
        'Delphinium.Dev',
        'Delphinium.Roots'
    ];
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Blossom',
            'description' => 'Plug-in used to develop view for displaying grade information',
            'author'      => 'Jacob Reid',
            'icon'        => 'icon-asterisk'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Delphinium\Blossom\Components\Grade' => 'grade',
			'\Delphinium\Blossom\Components\Bonus' => 'bonus',
            '\Delphinium\Blossom\Components\Experience' => 'experience',
            '\Delphinium\Blossom\Components\Leaderboard' => 'leaderboard',
            '\Delphinium\Blossom\Components\Competencies' => 'competencies',
            '\Delphinium\Blossom\Components\StudentsGraph' => 'studentsgraph',
        ];
    }
	
	
	public function boot()
    {
    	
    	Event::listen('backend.menu.extendItems', function($manager){	
    	
            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'Bonus' => [
					'label' => 'Bonus',
					'icon' => 'icon-heart',
					'owner' => 'Delphinium.Greenhouse',
					'url' => Backend::url('delphinium/blossom/bonus'),
                    'group'       => 'Blossom',
				],

                'Competencies' => [
                    'label' => 'Competencies',
                    'icon' => 'icon-sliders',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/competencies'),
                    'group'       => 'Blossom',
                ],

                'Experience' => [
                    'label' => 'Experience',
                    'icon' => 'icon-shield',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/experience'),
                    'group'       => 'Blossom',
                ],

                'Grade' => [
                    'label' => 'Grade',
                    'icon' => 'icon-signal',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/grade'),
                    'group'       => 'Blossom',
                ],

                'Leaderboard' => [
                    'label' => 'Leaderboard',
                    'icon' => 'icon-sitemap',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/leaderboard'),
                    'group'       => 'Blossom',
                ],

                'StudentsGraph' => [
                    'label' => 'StudentsGraph',
                    'icon' => 'icon-bar-chart',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/studentsgraph'),
                    'group'       => 'Blossom',
                ]
            ]);
            
        });
    }
}

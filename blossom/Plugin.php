<?php namespace Delphinium\Blossom;

use System\Classes\PluginBase;
use Backend;
use Event;

use Backend\Classes\FormWidgetBase;
use Delphinium\Blossom\FormWidgets\ColorPicker;
//use Backend\formwidgets\ColorPicker;
//use Backend\widgets\form\Form;// checkbox & radio ?

/**
 * Blossom Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Delphinium.Greenhouse',
        'Delphinium.Dev',
        'Delphinium.Roots',
        'Delphinium.Xylum'
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
            'description' => 'Plug-in used to develop views for displaying grade information',
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
            '\Delphinium\Blossom\Components\Timer' => 'timer',
            '\Delphinium\Blossom\Components\Progress' => 'progress',
            '\Delphinium\Blossom\Components\ExperienceManager' => 'experiencemanager',
            '\Delphinium\Blossom\Components\Gradebook' => 'gradebook',
            '\Delphinium\Blossom\Components\EasterEggs' => 'eastereggs',
            '\Delphinium\Blossom\Components\Stats' => 'stats'
        ];
    }
	
    public function registerFormWidgets()
    {
        return[
            'Delphinium\Blossom\FormWidgets\ColorPicker' => [
                'label' => 'Color picker',
                'code' => 'colorpicker'
            ]
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
                ],

                'Timer' => [
                    'label' => 'Timer',
                    'icon' => 'icon-bar-chart',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/timer'),
                    'group'       => 'Blossom',
                ],

                'Progress' => [
                    'label' => 'Progress',
                    'icon' => 'icon-bar-chart',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/progress'),
                    'group'       => 'Blossom',
                ],

                'Easter Eggs' => [
                    'label' => 'Easter Eggs',
                    'icon' => 'icon-bar-chart',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/eastereggs'),
                    'group' =>'Blossom',
                ],

                'Stats' => [
                    'label' => 'Stats',
                    'icon' => 'icon-bar-chart',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/blossom/stats'),
                    'group'       => 'Blossom',
                ]
            ]);
            
        });
    }
}

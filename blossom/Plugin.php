<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Blossom;

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

                'Easter Eggs' => [
                    'label' => 'Easter Eggs',
                    'icon' => 'icon-lemon-o',
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

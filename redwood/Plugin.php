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

namespace Delphinium\Redwood;

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
            'Delphinium\Redwood\Components\PMOauth' => 'PMOauth',
            'Delphinium\Redwood\Components\TestRedwoodRoots' => 'testRedwoodRoots',
            'Delphinium\Redwood\Components\Processmaker' => 'processmaker'
        ];
    }


    public function boot()
    {
        Event::listen('backend.menu.extendItems', function($manager) {

            $manager->addSideMenuItems('Delphinium.Greenhouse', 'greenhouse', [
                'PMOAuth' => [
                    'label' => 'PM Oauth',
                    'icon' => 'icon-key',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/redwood/pmoauth'),
                    'group'       => 'Redwood',
                ],
                'Processmaker' => [
                    'label' => 'ProcessMaker',
                    'icon' => 'icon-key',
                    'owner' => 'Delphinium.Greenhouse',
                    'url' => Backend::url('delphinium/redwood/processmaker'),
                    'group'       => 'Redwood',
                ]
            ]);

        });
    }
}

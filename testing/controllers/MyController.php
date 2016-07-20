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

namespace Delphinium\Testing\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Testing\Widgets\Delphiniumize;
use System\Classes\PluginManager;

/**
 * My Controller Back-end Controller
 */
class MyController extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

//        BackendMenu::setContext('Delphinium.Testing', 'testing', 'mycontroller');
        BackendMenu::setContext('Delphinium.Greenhouse', 'greenhouse', 'greenhouse');

        $plugins = $this->getPluginList();
        new Delphiniumize($this, 'delphiniumize');
    }

    protected function getPluginList()
    {
        $plugins = PluginManager::instance()->getPlugins();

        $result = [];
        foreach ($plugins as $code=>$plugin) {
            $pluginInfo = $plugin->pluginDetails();

            $itemInfo = [
                'name' => isset($pluginInfo['name']) ? $pluginInfo['name'] : 'No name provided',
                'description' => isset($pluginInfo['description']) ? $pluginInfo['description'] : 'No description provided',
                'icon' => isset($pluginInfo['icon']) ? $pluginInfo['icon'] : null
            ];

            list($namespace) = explode('\\', get_class($plugin));
            $itemInfo['namespace'] = trim($namespace);
            $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);

            $result[$code] = $itemInfo;
        }

        uasort($result, function($a, $b) {
            return strcmp(trans($a['name']), trans($b['name']));
        });

        return $result;
    }
}
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

namespace Delphinium\Greenhouse;

use Backend;
Use Event;
use System\Classes\PluginBase;
use October\Rain\Support\Traits\Emitter;
use BackendMenu;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Greenhouse Plugin',
            'description' => 'Provides gamification widgets for learning suites',
            'author' => 'Damaris Zarco',
            'icon' => 'icon-leaf'
        ];
    }

    
    public function boot()
    {   
    	//here we fire the event so all other plugins can register
    	Event::fire('delphinium.greenhouse.load');
    	 
    }

    public function register()
    {
<<<<<<< HEAD
<<<<<<< HEAD
        BackendMenu::registerContextSidenavPartial('Delphinium.Greenhouse', 'greenhouse', '$/delphinium/greenhouse/partials/_sidebar.htm');
=======
        BackendMenu::registerContextSidenavPartial('Delphinium.Greenhouse', 'greenhouse', '~/plugins/delphinium/greenhouse/partials/_sidebar.htm');
>>>>>>> b2e80ac3a6c3869578b563140dcb761ad5044817
=======
        BackendMenu::registerContextSidenavPartial('Delphinium.Greenhouse', 'greenhouse', '~/plugins/delphinium/greenhouse/partials/_sidebar.htm');
>>>>>>> 458e203e636a22db079d0e2b12c60aa91cba5e3b

        //register console commands
        $this->registerConsoleCommand('Delphinium.DelphiniumPlugin', 'Delphinium\Greenhouse\Console\DelphiniumPlugin');
        $this->registerConsoleCommand('Delphinium.DelphiniumComponent', 'Delphinium\Greenhouse\Console\DelphiniumComponent');
        $this->registerConsoleCommand('Delphinium.DelphiniumController', 'Delphinium\Greenhouse\Console\DelphiniumController');
        $this->registerConsoleCommand('Delphinium.DelphiniumModel', 'Delphinium\Greenhouse\Console\DelphiniumModel');
    }
    
    public function registerNavigation()
	{
    	return [
            'greenhouse' => [
                'label'       => 'Greenhouse',
                'url'         => Backend::url('delphinium/greenhouse/greenhouse'),
                'icon'        => 'icon-leaf',
                'order'       => 500,
            ]
        ];
	}
	
}
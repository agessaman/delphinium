<?php namespace Delphinium\Editor\Controllers;
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

use Request;
use BackendMenu;
use Backend\Classes\Controller;
use Delphinium\Editor\Classes\Plugin;
use Delphinium\Editor\Widgets\PluginList;
use Delphinium\Editor\Widgets\AssetList;
use Delphinium\Editor\Widgets\ComponentList;
use Delphinium\Editor\Widgets\DelphiniumizeList;

/**
 * Index Back-end Controller
 */
class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Delphinium.Editor', 'editor', 'editor');

        //plugins directory
        $destinationPath = '/plugins/';

        $this->plugin = Plugin::load($destinationPath);
        try {
            //this is the plugin list from builder. Used to select the active plugin
            new PluginList($this, 'pluginList');
            new ComponentList($this, 'componentList');
            new AssetList($this, 'assetList', $destinationPath);
            new DelphiniumizeList($this, 'delphiniumizeList');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return;
    }

    public function index()
    {
//        $this->addJs('/modules/backend/assets/js/october.filelist.js', 'core');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.cmspage.js', 'core');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.dragcomponents.js', 'core');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.tokenexpander.js', 'core');
//        $this->addCss('/plugins/delphinium/vanilla/assets/css/october.components.css', 'core');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/plugin.js');
//        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');
//        // Preload the code editor class as it could be needed
//        // before it loads dynamically.
//        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');
//
//        //we require the table widget
        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');
        $this->addJs('/plugins/delphinium/vanilla/assets/js/build-min.js', 'Delphinium.Vanilla');

        $this->bodyClass = 'compact-container side-panel-not-fixed';
        $this->pageTitle = 'Editor';
        $this->pageTitleTemplate = '%s '.trans($this->pageTitle);

        if (Request::ajax() && Request::input('formWidgetAlias')) {
            $this->bindFormWidgetToController();
        }
    }
}
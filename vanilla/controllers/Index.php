<?php namespace Delphinium\Vanilla\Controllers;
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

use File;
use Lang;
use Cache;
use Config;
use Validator;
use BackendMenu;
use Request;
use Backend\Classes\Controller;
use Delphinium\Vanilla\Widgets\Delphiniumize as Widget;
//use Delphinium\Vanilla\Widgets\ComponentsList;
use Delphinium\Vanilla\Widgets\AssetsList;
use Backend\FormWidgets\CodeEditor;
use Backend\Classes\FormField;
//use Cms\Widgets\AssetList;
use Cms\Widgets\ComponentList;


/**
 * Index Back-end Controller
 */
class Index extends Controller
{

    use \Backend\Traits\InspectableContainer;

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'vanilla');
        new Widget($this, 'delphiniumize');
        new ComponentList($this, 'componentsList');

        $theme = $this->theme;
        new AssetsList($this, 'assetsList', $this->getFilesInPlugin($theme));



        //public function __construct($controller, $formField, $configuration = [])
//        $defaultBuilderField = new FormField('default', 'default');
//        $codeEditor = new CodeEditor($this,$defaultBuilderField,[]);
//        $codeEditor->init();
//        $codeEditor->render();



    }
    public function index()
    {
        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');
        $this->addJs('/plugins/rainlab/pages/assets/js/pages-page.js');
        $this->addJs('/plugins/rainlab/pages/assets/js/pages-snippets.js');
        $this->addCss('/plugins/rainlab/pages/assets/css/pages.css');

        // Preload the code editor class as it could be needed
        // before it loads dynamically.
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');

        $this->bodyClass = 'compact-container side-panel-not-fixed';
        $this->pageTitle = 'rainlab.pages::lang.plugin.name';
        $this->pageTitleTemplate = '%s Pages';

        if (Request::ajax() && Request::input('formWidgetAlias')) {
            $this->bindFormWidgetToController();
        }
    }

    /**
     * Returns the list of objects in the specified theme.
     * This method is used internally by the system.
     * @param \Cms\Classes\Theme $theme Specifies a parent theme.
     * @param boolean $skipCache Indicates if objects should be reloaded from the disk bypassing the cache.
     * @return array Returns an array of CMS objects.
     */
    public static function getFilesInPlugin($theme, $skipCache = false)
    {
//        if (!$theme) {
//            throw new ApplicationException(Lang::get('cms::lang.theme.active.not_set'));
//        }

//        $dirPath = $theme->getPath().'/'.static::getObjectTypeDirName();
        $dirPath ="C:\\wamp\\www\\delphinium/plugins/blossom";
        echo $dirPath;
        $result = [];

        if (!File::isDirectory($dirPath)) {
            return $result;
        }

        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
        $it->setMaxDepth(1); // Support only a single level of subdirectories
        $it->rewind();

        while ($it->valid()) {
            if ($it->isFile() && in_array($it->getExtension(), static::$allowedExtensions)) {
                $filePath = $it->getBasename();
                if ($it->getDepth() > 0) {
                    $filePath = basename($it->getPath()).'/'.$filePath;
                }

                $page = $skipCache ? static::load($theme, $filePath) : static::loadCached($theme, $filePath);
                $result[] = $page;
            }

            $it->next();
        }

        return $result;
    }


    protected function bindFormWidgetToController()
    {
        $alias = Request::input('formWidgetAlias');
        $type = Request::input('objectType');
        $object = $this->loadObject($type, Request::input('objectPath'));

        $widget = $this->makeObjectFormWidget($type, $object, $alias);
        $widget->bindToController();
    }

    //from builder
    //RainLab\Builder\Classes\IndexOperationsBehaviorBase;
    //this behavior is being called from javascript from this file:
    //builder/assets/js/builder.index.entity.version.js.
    //from the following function:
    //Version.prototype.cmdOpenVersion = function(ev) {
    //var versionNumber = $(ev.currentTarget).data('id'),
    //pluginCode = $(ev.currentTarget).data('pluginCode')
    //
    //this.indexController.openOrLoadMasterTab($(ev.target), 'onVersionCreateOrOpen', this.makeTabId(pluginCode+'-'+versionNumber), {
    //original_version: versionNumber
    //})
    //}

    //onVersionCreateOrOpen is here:
    //RainLab\Builder\Behaviors\IndexVersionsOperations;
    //public function onVersionCreateOrOpen()
    protected function makeBaseFormWidget($modelCode, $options = [])
    {
        if (!strlen($this->baseFormConfigFile)) {
            throw new ApplicationException(sprintf('Base form configuration file is not specified for %s behavior', get_class($this)));
        }

        $widgetConfig = $this->makeConfig($this->baseFormConfigFile);

        $widgetConfig->model = $this->loadOrCreateBaseModel($modelCode, $options);
        $widgetConfig->alias = 'form_'.md5(get_class($this)).uniqid();

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = strlen($modelCode) ? FormController::CONTEXT_UPDATE : FormController::CONTEXT_CREATE;

        return $form;
    }
}
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
use Cms\Classes\Theme;
use ApplicationException;
//use Delphinium\Vanilla\Widgets\ComponentsList;
use Delphinium\Vanilla\Widgets\AssetsList;
use Backend\FormWidgets\CodeEditor;
use Backend\Classes\FormField;
use Cms\Widgets\ComponentList;
//use Cms\Widgets\AssetList;
//use Cms\Widgets\TemplateList;


/**
 * Index Back-end Controller
 */
class Index extends Controller
{
    use \Backend\Traits\InspectableContainer;

    protected $theme;


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'vanilla');

        if (!($theme = Theme::getEditTheme())) {
//            throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
        }

        $this->theme = $theme;
        //plugins directory
        $destinationPath = '/plugins/delphinium/blossom';

        try {
            new ComponentList($this, 'componentList');
            new AssetsList($this, 'assetsList', $destinationPath);
            new Widget($this, 'delphiniumize');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return;
        new Widget($this, 'delphiniumize');
        new ComponentList($this, 'componentsList');
        new AssetsList($this, 'assetsList', $this->getFilesInPlugin($theme));



        //public function __construct($controller, $formField, $configuration = [])
//        $defaultBuilderField = new FormField('default', 'default');
//        $codeEditor = new CodeEditor($this,$defaultBuilderField,[]);
//        $codeEditor->init();
//        $codeEditor->render();



    }

    public function index()
    {
        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.cmspage.js', 'core');
        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.dragcomponents.js', 'core');
        $this->addJs('/plugins/delphinium/vanilla/assets/js/october.tokenexpander.js', 'core');
        $this->addCss('/plugins/delphinium/vanilla/assets/css/october.components.css', 'core');

        // Preload the code editor class as it could be needed
        // before it loads dynamically.
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');

        $this->bodyClass = 'compact-container side-panel-not-fixed';
        $this->pageTitle = 'Vanilla';
        $this->pageTitleTemplate = '%s '.trans($this->pageTitle);

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
    public static function getFilesInPlugin($dirPath, $skipCache = false)
    {
//        if (!$theme) {
//            throw new ApplicationException(Lang::get('cms::lang.theme.active.not_set'));
//        }

//        $dirPath = $theme->getPath().'/'.static::getObjectTypeDirName();
//        $dirPath ="C:\\wamp\\www\\delphinium/plugins/blossom";

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


    public function index_onOpenTemplate()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $template = $this->loadTemplate($type, Request::input('path'));
        $widget = $this->makeTemplateFormWidget($type, $template);

        $this->vars['templatePath'] = Request::input('path');

        if ($type == 'page') {
            $router = new RainRouter;
            $this->vars['pageUrl'] = $router->urlFromPattern($template->url);
        }

        return [
            'tabTitle' => $this->getTabTitle($type, $template),
            'tab'      => $this->makePartial('form_page', [
                'form'          => $widget,
                'templateType'  => $type,
                'templateTheme' => $this->theme->getDirName(),
                'templateMtime' => $template->mtime
            ])
        ];
    }

    public function onSave()
    {
        $this->validateRequestTheme();
        $type = Request::input('templateType');
        $templatePath = trim(Request::input('templatePath'));
        $template = $templatePath ? $this->loadTemplate($type, $templatePath) : $this->createTemplate($type);

        $settings = Request::input('settings') ?: [];
        $settings = $this->upgradeSettings($settings);

        $templateData = [];
        if ($settings) {
            $templateData['settings'] = $settings;
        }

        $fields = ['markup', 'code', 'fileName', 'content'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $_POST)) {
                $templateData[$field] = Request::input($field);
            }
        }

        if (!empty($templateData['markup']) && Config::get('cms.convertLineEndings', false) === true) {
            $templateData['markup'] = $this->convertLineEndings($templateData['markup']);
        }

        if (!Request::input('templateForceSave') && $template->mtime) {
            if (Request::input('templateMtime') != $template->mtime) {
                throw new ApplicationException('mtime-mismatch');
            }
        }

        $template->fill($templateData);
        $template->save();

        /*
         * Extensibility
         */
        Event::fire('cms.template.save', [$this, $template, $type]);
        $this->fireEvent('template.save', [$template, $type]);

        Flash::success(Lang::get('cms::lang.template.saved'));

        $result = [
            'templatePath'  => $template->fileName,
            'templateMtime' => $template->mtime,
            'tabTitle'      => $this->getTabTitle($type, $template)
        ];

        if ($type == 'page') {
            $result['pageUrl'] = URL::to($template->url);
            $router = new Router($this->theme);
            $router->clearCache();
            CmsCompoundObject::clearCache($this->theme);
        }

        return $result;
    }

    public function onOpenConcurrencyResolveForm()
    {
        return $this->makePartial('concurrency_resolve_form');
    }

    public function onCreateTemplate()
    {
        $type = Request::input('type');
        $template = $this->createTemplate($type);

        if ($type == 'asset') {
            $template->setInitialPath($this->widget->assetList->getCurrentRelativePath());
        }

        $widget = $this->makeTemplateFormWidget($type, $template);

        $this->vars['templatePath'] = '';

        return [
            'tabTitle' => $this->getTabTitle($type, $template),
            'tab'   => $this->makePartial('form_page', [
                'form'          => $widget,
                'templateType'  => $type,
                'templateTheme' => $this->theme->getDirName(),
                'templateMtime' => null
            ])
        ];
    }

    public function onDeleteTemplates()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $templates = Request::input('template');
        $error = null;
        $deleted = [];

        try {
            foreach ($templates as $path => $selected) {
                if ($selected) {
                    $this->loadTemplate($type, $path)->delete();
                    $deleted[] = $path;
                }
            }
        }
        catch (Exception $ex) {
            $error = $ex->getMessage();
        }

        /*
         * Extensibility
         */
        Event::fire('cms.template.delete', [$this, $type]);
        $this->fireEvent('template.delete', [$type]);

        return [
            'deleted' => $deleted,
            'error'   => $error,
            'theme'   => Request::input('theme')
        ];
    }

    public function onDelete()
    {
        $this->validateRequestTheme();

        $type = Request::input('templateType');

        $this->loadTemplate($type, trim(Request::input('templatePath')))->delete();

        /*
         * Extensibility
         */
        Event::fire('cms.template.delete', [$this, $type]);
        $this->fireEvent('template.delete', [$type]);
    }

    public function onGetTemplateList()
    {
        $this->validateRequestTheme();

        $page = new Page($this->theme);
        return [
            'layouts' => $page->getLayoutOptions()
        ];
    }

    public function onExpandMarkupToken()
    {
        if (!$alias = post('tokenName')) {
            throw new ApplicationException(trans('cms::lang.component.no_records'));
        }

        // Can only expand components at this stage
        if ((!$type = post('tokenType')) && $type != 'component') {
            return;
        }

        if (!($names = (array) post('component_names')) || !($aliases = (array) post('component_aliases'))) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        if (($index = array_get(array_flip($aliases), $alias, false)) === false) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        if (!$componentName = array_get($names, $index)) {
            throw new ApplicationException(trans('cms::lang.component.not_found', ['name' => $alias]));
        }

        $manager = ComponentManager::instance();
        $componentObj = $manager->makeComponent($componentName);
        $partial = ComponentPartial::load($componentObj, 'default');
        $content = $partial->getContent();
        $content = str_replace('__SELF__', $alias, $content);

        return $content;
    }

    protected function validateRequestTheme()
    {
        return;
//        if ($this->theme->getDirName() != Request::input('theme')) {
//            throw new ApplicationException(trans('cms::lang.theme.edit.not_match'));
//        }
    }

    protected function resolveTypeClassName($type)
    {
        $types = [
            'page'    => '\Cms\Classes\Page',
            'partial' => '\Cms\Classes\Partial',
            'layout'  => '\Cms\Classes\Layout',
            'content' => '\Cms\Classes\Content',
            'asset'   => '\Cms\Classes\Asset'
        ];

        if (!array_key_exists($type, $types)) {
            throw new ApplicationException(trans('cms::lang.template.invalid_type'));
        }

        return $types[$type];
    }
    protected function loadTemplate($type, $path)
    {
        $class = $this->resolveTypeClassName($type);

//        echo $type;
//        echo $path;
//        echo json_encode($class);
//        echo json_encode($this->theme);
//        echo "-----";
//        $template = call_user_func(array($class, 'load'), $this->theme, $path);
//        echo json_encode($template);
//        echo "end";


        if (!($template = call_user_func(array($class, 'load'), $this->theme, $path))) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        Event::fire('cms.template.processSettingsAfterLoad', [$this, $template]);

        return $template;
    }

    protected function createTemplate($type)
    {
        $class = $this->resolveTypeClassName($type);

        if (!($template = new $class($this->theme))) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        return $template;
    }

    protected function getTabTitle($type, $template)
    {
        if ($type == 'page') {
            $result = $template->title ?: $template->getFileName();
            if (!$result) {
                $result = trans('cms::lang.page.new');
            }

            return $result;
        }

        if ($type == 'partial' || $type == 'layout' || $type == 'content' || $type == 'asset') {
            $result = in_array($type, ['asset', 'content']) ? $template->getFileName() : $template->getBaseFileName();
            if (!$result) {
                $result = trans('cms::lang.'.$type.'.new');
            }

            return $result;
        }

        return $template->getFileName();
    }

    protected function makeTemplateFormWidget($type, $template, $alias = null)
    {
        $formConfigs = [
            'page'    => '~/modules/cms/classes/page/fields.yaml',
            'partial' => '~/modules/cms/classes/partial/fields.yaml',
            'layout'  => '~/modules/cms/classes/layout/fields.yaml',
            'content' => '~/modules/cms/classes/content/fields.yaml',
            'asset'   => '~/modules/cms/classes/asset/fields.yaml'
        ];

        if (!array_key_exists($type, $formConfigs)) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        $widgetConfig = $this->makeConfig($formConfigs[$type]);
        $widgetConfig->model = $template;
        $widgetConfig->alias = $alias ?: 'form'.studly_case($type).md5($template->getFileName()).uniqid();

        $widget = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        return $widget;
    }

    protected function getFilePathmakeTemplateFormWidget($type, $template, $alias = null)
    {
        $formConfigs = [
            'page'    => '~/modules/cms/classes/page/fields.yaml',
            'partial' => '~/modules/cms/classes/partial/fields.yaml',
            'layout'  => '~/modules/cms/classes/layout/fields.yaml',
            'content' => '~/modules/cms/classes/content/fields.yaml',
            'asset'   => '~/modules/cms/classes/asset/fields.yaml'
        ];

        if (!array_key_exists($type, $formConfigs)) {
            throw new ApplicationException(trans('cms::lang.template.not_found'));
        }

        $widgetConfig = $this->makeConfig($formConfigs[$type]);
        $widgetConfig->model = $template;
        $widgetConfig->alias = $alias ?: 'form'.studly_case($type).md5($template->getFileName()).uniqid();

        $widget = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        return $widget;
    }
}
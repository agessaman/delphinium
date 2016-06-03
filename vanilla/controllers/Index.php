<?php namespace Delphinium\Vanilla\Controllers;

use Request;
use BackendMenu;
use ApplicationException;
use Backend\Classes\Controller;
use Delphinium\Vanilla\Classes\Plugin;
use Delphinium\Vanilla\Widgets\PluginList;
use Delphinium\Vanilla\Widgets\ComponentList;
use Delphinium\Vanilla\Widgets\DelphiniumizeList;
use Delphinium\Vanilla\Widgets\AssetList;

/**
 * Index Back-end Controller
 */
class Index extends Controller
{
    public $relativePluginDir;
    public $plugin;
    public $implement = [
        'Delphinium.Vanilla.Behaviors.IndexPluginOperations'
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Delphinium.Vanilla', 'vanilla', 'index');

        //plugins directory
        $destinationPath = '/plugins/';
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
        $this->addCss('/plugins/delphinium/vanilla/assets/css/october.components.css', 'core');

        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');
//        $this->addJs('/modules/cms/assets/js/october.cmspage.js', 'core');

        //This file is what manages the plugin selection but it interferes with the tabs, so that needs to be worked on
        $this->addJs('/plugins/delphinium/vanilla/assets/js/build-min.js', 'Delphinium.Vanilla');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/builder.index.js', 'Delphinium.Vanilla');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/builder.index.entity.base.js', 'Delphinium.Vanilla');
//        $this->addJs('/plugins/delphinium/vanilla/assets/js/builder.index.entity.plugin.js', 'Delphinium.Vanilla');

        //this file below is what manages the display of files in the code editor
        $this->addJs('/plugins/delphinium/vanilla/assets/js/pages-page.js', 'Delphinium.Vanilla');

        //This is needed to expand the components menu and display the components inside each plugin
        $this->addJs('/modules/backend/assets/js/october.filelist.js', 'core');

        $this->bodyClass = 'compact-container side-panel-not-fixed';
        $this->pageTitle = 'Vanilla';
        $this->pageTitleTemplate = '%s '.trans($this->pageTitle);

        if (Request::ajax() && Request::input('formWidgetAlias')) {
            $this->bindFormWidgetToController();
        }
    }

    public function setBuilderActivePlugin($pluginCode, $refreshPluginList = false)
    {
        $this->widget->pluginList->setActivePlugin($pluginCode);

        $result = [];
        if ($refreshPluginList) {
            $result = $this->widget->pluginList->updateList();
        }

        $result = array_merge(
            $result,
            $this->widget->assetList->refreshActivePlugin(),
            $this->widget->componentList->refreshActivePlugin(),
            $this->widget->delphiniumizeList->refreshActivePlugin()
        );

        return $result;
    }

    public function getBuilderActivePluginVector()
    {
        return $this->widget->pluginList->getActivePluginVector();
    }

    public function updatePluginList()
    {
        return $this->widget->pluginList->updateList();
    }

//    public function index_onOpenTemplate_()
//    {
//        return "ici";
//        $this->validateRequestTheme();
//
//        $type = Request::input('type');
//        $template = $this->loadTemplate($type, Request::input('path'));
//        $widget = $this->makeTemplateFormWidget($type, $template);
//
//        $this->vars['templatePath'] = Request::input('path');
//
//        if ($type == 'page') {
//            $router = new RainRouter;
//            $this->vars['pageUrl'] = $router->urlFromPattern($template->url);
//        }
//
//        return [
//            'tabTitle' => $this->getTabTitle($type, $template),
//            'tab'      => $this->makePartial('form_page', [
//                'form'          => $widget,
//                'templateType'  => $type,
//                'templateTheme' => $this->theme->getDirName(),
//                'templateMtime' => $template->mtime
//            ])
//        ];
//    }
//


    public function index_onOpen()
    {
        $this->validateRequestTheme();

        $type = Request::input('type');
        $object = $this->loadObject($type, Request::input('path'));

        return $this->pushObjectForm($type, $object);
    }

    protected function validateRequestTheme()
    {
        return true;
//        if ($this->theme->getDirName() != Request::input('theme')) {
//            throw new ApplicationException(trans('cms::lang.theme.edit.not_match'));
//        }
    }

    protected function loadObject($type, $path, $ignoreNotFound = false)
    {
        $class = $this->resolveTypeClassName($type);
        $plugin = $this->pluginVectorToPluginClass();
        $this->plugin = $plugin;
        if (!($object = call_user_func(array($class, 'load'), $plugin, $path, $type))) {
            if (!$ignoreNotFound) {
                throw new ApplicationException(trans('rainlab.pages::lang.object.not_found'));
            }

            return null;
        }

        if ($type == 'content') {
            $fileName = $object->getFileName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            if ($extension == 'htm') {
                $object->markup_html = $object->markup;
            }
        }

        return $object;
    }


    protected function resolveTypeClassName($type)
    {
        $types = [
            'page'    => '\Delphinium\Vanilla\Classes\Page',
            'partial' => '\Delphinium\Vanilla\Classes\Partial',
//            'layout'  => '\Delphinium\Vanilla\Classes\Layout',
//            'content' => '\Delphinium\Vanilla\Classes\Content',
            'asset'   => '\Delphinium\Vanilla\Classes\Asset'
        ];

        if (!array_key_exists($type, $types)) {
            throw new ApplicationException(trans('cms::lang.template.invalid_type'));
        }

        return $types[$type];
    }

    protected function pushObjectForm($type, $object)
    {
        $widget = $this->makeObjectFormWidget($type, $object);

        $this->vars['objectPath'] = Request::input('path');

        if ($type == 'page') {
            $this->vars['pageUrl'] = URL::to($object->getViewBag()->property('url'));
        }

        $this->vars['templatePath'] = Request::input('path');

        return [
            'tabTitle' => $this->getTabTitle($type, $object),
            'tab'      => $this->makePartial('form_page', [
                'form'          => $widget,
                'templateType'  => $type,
                'templateTheme' => $this->plugin->getDirName(),
                'templateMtime' => $object->mtime
            ])
        ];
    }

    protected function makeObjectFormWidget($type, $object, $alias = null)
    {
        $formConfigs = [
            'page'    => '~/modules/cms/classes/page/fields.yaml',
            'partial' => '~/modules/cms/classes/partial/fields.yaml',
            'layout'  => '~/modules/cms/classes/layout/fields.yaml',
            'content' => '~/modules/cms/classes/content/fields.yaml',
            'asset'   => '~/modules/cms/classes/asset/fields.yaml'
        ];

        if (!array_key_exists($type, $formConfigs)) {
            throw new ApplicationException(trans('rainlab.pages::lang.object.not_found'));
        }

        $widgetConfig = $this->makeConfig($formConfigs[$type]);
        $widgetConfig->model = $object;
        $widgetConfig->alias = $alias ?: 'form'.studly_case($type).md5($object->getFileName()).uniqid();

        $widget = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        if ($type == 'page') {
            $widget->bindEvent('form.extendFieldsBefore', function() use ($widget, $object) {
                $this->addPagePlaceholders($widget, $object);
                $this->addPageSyntaxFields($widget, $object);
            });
        }

        return $widget;
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

    public function pluginVectorToPluginClass()
    {
        $pluginVector = $this->getBuilderActivePluginVector();
        $this->relativePluginDir =$pluginVector->pluginCodeObj->toFilesystemPath();
        $plugin = Plugin::load($this->relativePluginDir);
        $this->plugin = $plugin;
        return $plugin;
    }
}
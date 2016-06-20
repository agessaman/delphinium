<?php namespace Delphinium\Vanilla\Widgets;

use URL;
use Str;
use Lang;
use File;
use Config;
use Input;
use Request;
use Response;
use Cms\Classes\Theme;
use System\Classes\PluginManager;
use Cms\Classes\ComponentHelpers;
use Cms\Helpers\File as FileHelper;
use Backend\Classes\WidgetBase;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DirectoryIterator;

/**
 * Component list widget.
 *
 * @package Delphinium\Vanilla
 * @author Alexey Bobkov, Samuel Georges
 */
class ComponentList extends WidgetBase
{
    protected $searchTerm = false;

    protected $groupStatusCache = false;

    protected $pluginComponentList;

    protected $plugin;

    protected static $allowedExtensions = ['htm','php'];

    /**
     * @var string Message to display when there are no records in the list.
     */
    public $noRecordsMessage = 'No files found';

    /**
     * @var string Message to display when the Delete button is clicked.
     */
    public $deleteConfirmation = 'Do you really want to delete selected files or directories?';

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function render()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        return $this->makePartial('body', [
            'data' => $this->getData($activePluginVector),
            'pluginVector'=>$activePluginVector
        ]);
    }

    /**
     * Returns information about this widget, including name and description.
     */
    public function widgetDetails()
    {
    }

    /*
     * Event handlers
     */

    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));

        return $this->updateList();
    }

    public function onGroupStatusUpdate()
    {
        $this->setGroupStatus(Input::get('group'), Input::get('status'));
    }

    /*
     * Methods for th internal use
     */

    protected function getData($activePluginVector)
    {
        if(!$activePluginVector)
        {
            return;
        }
        else
        {
            $this->plugin =$this->controller->pluginVectorToPluginClass();
        }

        $searchTerm = Str::lower($this->getSearchTerm());
        $searchWords = [];
        if (strlen($searchTerm)) {
            $searchWords = explode(' ', $searchTerm);
        }

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $this->prepareComponentList($activePluginVector);

        $items = [];
        foreach ($plugins as $plugin) {
            $components = $this->getPluginComponents($plugin);
            if (!is_array($components)) {
                continue;
            }

            $pluginDetails = $plugin->pluginDetails();

            $pluginName = isset($pluginDetails['name'])
                ? $pluginDetails['name']
                : Lang::get('system::lang.plugin.unnamed');

            $pluginIcon = isset($pluginDetails['icon'])
                ? $pluginDetails['icon']
                : 'icon-puzzle-piece';

            $pluginDescription = isset($pluginDetails['description'])
                ? $pluginDetails['description']
                : null;

            $pluginClass = get_class($plugin);

            $pluginItems = [];
            foreach ($components as $componentInfo) {
                $className = $componentInfo->className;
                $alias = $componentInfo->alias;
                $component = new $className();

                if ($component->isHidden) {
                    continue;
                }

                $componentDetails = $component->componentDetails();
                $component->alias = '--alias--';

                //get this component's files
                $files = $this->getComponentFiles($componentInfo);
                $item = (object)[
                    'title'          => ComponentHelpers::getComponentName($component),
                    'description'    => ComponentHelpers::getComponentDescription($component),
                    'plugin'         => $pluginName,
                    'propertyConfig' => ComponentHelpers::getComponentsPropertyConfig($component),
                    'propertyValues' => ComponentHelpers::getComponentPropertyValues($component, $alias),
                    'className'      => get_class($component),
                    'pluginIcon'     => $pluginIcon,
                    'alias'          => $alias,
                    'name'           => $componentInfo->duplicateAlias
                        ? $componentInfo->className
                        : $componentInfo->alias,
                    'files'         => $files
                ];

                if ($searchWords && !$this->itemMatchesSearch($searchWords, $item)) {
                    continue;
                }

                if (!array_key_exists($pluginClass, $items)) {
                    $group = (object)[
                        'title'       => $pluginName,
                        'description' => $pluginDescription,
                        'pluginClass' => $pluginClass,
                        'icon'        => $pluginIcon,
                        'items'       => []
                    ];

                    $items[$pluginClass] = $group;
                }

                $pluginItems[] = $item;
            }

            usort($pluginItems, function ($a, $b) {
                return strcmp($a->title, $b->title);
            });

            if (isset($items[$pluginClass])) {
                $items[$pluginClass]->items = $pluginItems;
            }
        }

        uasort($items, function ($a, $b) {
            return strcmp($a->title, $b->title);
        });
        return $items;
    }

    protected function prepareComponentList($activePluginVector)
    {
        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $componentList = [];
        foreach ($plugins as $key=>$plugin) {
            if($key!==$activePluginVector->pluginCodeObj->toCode()) {
                continue;
            }
            $components = $plugin->registerComponents();
            if (!is_array($components)) {
                continue;
            }

            foreach ($components as $className => $alias) {
                $duplicateAlias = false;
                foreach ($componentList as $componentInfo) {
                    if ($componentInfo->alias == $alias) {
                        $componentInfo->duplicateAlias = true;
                        $duplicateAlias = true;
                    }
                }

                $componentList[] = (object)[
                    'className'      => $className,
                    'alias'          => $alias,
                    'duplicateAlias' => $duplicateAlias,
                    'pluginClass'    => get_class($plugin)
                ];
            }
        }

        $this->pluginComponentList = $componentList;
    }

    protected function getPluginComponents($plugin)
    {
        $result = array();
        $pluginClass = get_class($plugin);
        foreach ($this->pluginComponentList as $componentInfo) {
            if ($componentInfo->pluginClass == $pluginClass) {
                $result[] = $componentInfo;
            }
        }
        return $result;
    }

    public function getComponentFiles($activeComponent)
    {
        $pluginsPath = \Config::get('cms.pluginsPath');
        $dirPath = base_path().$pluginsPath.$activeComponent->className;

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

                $page = static::load($dirPath,$filePath, $activeComponent);// loading it from cache : static::loadCached($theme, $filePath);
                $result[] = $page;
            }

            $it->next();
        }
        return $result;
    }

    /**
     * Loads the object from a file.
     * This method is used in the CMS back-end. It doesn't use any caching.
     * @param \Cms\Classes\Theme $theme Specifies the theme the object belongs to.
     * @param string $fileName Specifies the file name, with the extension.
     * The file name can contain only alphanumeric symbols, dashes and dots.
     * @return mixed Returns a CMS object instance or null if the object wasn't found.
     */
    public static function load($dirPath,$fileName, $activeComponent)
    {
        if (!FileHelper::validatePath($fileName, static::getMaxAllowedPathNesting())) {
            throw new ApplicationException(Lang::get('cms::lang.cms_object.invalid_file', ['name'=>$fileName]));
        }

        if (!strlen(File::extension($fileName))) {
            $fileName .= '.'.static::$defaultExtension;
        }

        $fullPath =$dirPath."\\".$fileName;//static::getFilePath($theme, $fileName);

        if (!File::isFile($fullPath)) {
            return null;
        }

        if (($content = @File::get($fullPath)) === false) {
            return null;
        }

        $obj = new \stdClass();//new static($theme);
        $obj->fileName = $fileName;
        $obj->path = "/components/".$activeComponent->alias."/".$fileName;
        $obj->fullPath = $dirPath."\\".$fileName;
        $obj->originalFileName = $fileName;
        $obj->mtime = File::lastModified($fullPath);
        $obj->content = $content;
        return $obj;
    }

    /**
     * Returns the maximum allowed path nesting level.
     * The default value is 2, meaning that files
     * can only exist in the root directory, or in a subdirectory.
     * @return mixed Returns the maximum nesting level or null if any level is allowed.
     */
    protected static function getMaxAllowedPathNesting()
    {
        return 2;
    }


    protected function getSearchTerm()
    {
        return $this->searchTerm !== false ? $this->searchTerm : $this->getSession('search');
    }

    protected function setSearchTerm($term)
    {
        $this->searchTerm = trim($term);
        $this->putSession('search', $this->searchTerm);
    }

    protected function updateList()
    {
        return ['#'.$this->getId('component-list') => $this->makePartial('items', ['items'=>$this->getData()])];
    }

    public function refreshActivePlugin()
    {
        $activePluginVector = $this->controller->getBuilderActivePluginVector();
        return ['#'.$this->getId('body') => $this->makePartial('body', ['data'=>$this->getData($activePluginVector), 'pluginVector'=>$activePluginVector])];
    }

    protected function itemMatchesSearch(&$words, $item)
    {
        foreach ($words as $word) {
            $word = trim($word);
            if (!strlen($word)) {
                continue;
            }

            if (!$this->itemContainsWord($word, $item)) {
                return false;
            }
        }

        return true;
    }

    protected function itemContainsWord($word, $item)
    {
        if (Str::contains(Str::lower($item->title), $word)) {
            return true;
        }

        if (Str::contains(Str::lower($item->description), $word) && strlen($item->description)) {
            return true;
        }

        if (Str::contains(Str::lower($item->plugin), $word) && strlen($item->plugin)) {
            return true;
        }

        return false;
    }

    protected function getGroupStatuses()
    {
        if ($this->groupStatusCache !== false) {
            return $this->groupStatusCache;
        }

        $groups = $this->getSession('groups');
        if (!is_array($groups)) {
            return $this->groupStatusCache = [];
        }

        return $this->groupStatusCache = $groups;
    }

    protected function setGroupStatus($group, $status)
    {
        $statuses = $this->getGroupStatuses();
        $statuses[$group] = $status;
        $this->groupStatusCache = $statuses;

        $this->putSession('groups', $statuses);
    }

    protected function getGroupStatus($group)
    {
        $statuses = $this->getGroupStatuses();
        if (array_key_exists($group, $statuses)) {
            return $statuses[$group];
        }

        return false;
    }

    protected function getPluginFileUrl($path)
    {
        $pluginsPath = Config::get('cms.pluginsPath');
        return URL::to($pluginsPath.'/'.$this->plugin->getDirName().'/assets/'.$path);
    }
}

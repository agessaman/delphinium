<?php namespace Delphinium\Vanilla\Widgets;

use Str;
use File;
use Lang;
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
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class ComponentList extends WidgetBase
{
    protected $searchTerm = false;

    protected $groupStatusCache = false;

    protected $pluginComponentList;

    protected $activePluginVector;

    protected static $fillable = [
        'content',
        'fileName'
    ];

    protected static $allowedExtensions = ['htm'];

    protected static $defaultExtension = 'htm';

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
        $activePluginVector = $this->getActivePlugin();
        $this->activePluginVector = $activePluginVector;
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


    public function getActivePlugin()
    {
        return $this->controller->getBuilderActivePluginVector();
    }

    protected function getData($activePluginVector)
    {
        if(!$activePluginVector)
        {
            return;
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

        foreach ($plugins as $key=>$plugin) {
            if($key!==$activePluginVector->pluginCodeObj->toCode()) {
               continue;
            }
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
                $this->getComponentFiles($plugin,$componentInfo);



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
                        : $componentInfo->alias
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

    public function getComponentFiles($activePlugin,$activeComponent)
    {
        $dirPath = base_path()."/plugins/".$activeComponent->className;

        $result = [];

        if (!File::isDirectory($dirPath)) {
            return $result;
        }

        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
        $it->setMaxDepth(1); // Support only a single level of subdirectories
        $it->rewind();

        while ($it->valid()) {
//            if ($it->isFile() && in_array($it->getExtension(), static::$allowedExtensions)) {
            if ($it->isFile()) {
                $filePath = $it->getBasename();
                if ($it->getDepth() > 0) {
                    $filePath = basename($it->getPath()).'/'.$filePath;
                }
echo $filePath;
                $page = static::load($filePath);// loading it from cache : static::loadCached($theme, $filePath);
                $result[] = $page;
            }

            $it->next();
        }
//echo json_encode($result);
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
    public static function load($fileName)
    {
        if (!FileHelper::validatePath($fileName, static::getMaxAllowedPathNesting())) {
            throw new ApplicationException(Lang::get('cms::lang.cms_object.invalid_file', ['name'=>$fileName]));
        }

        if (!strlen(File::extension($fileName))) {
            $fileName .= '.'.static::$defaultExtension;
        }

        $fullPath = $fileName;//static::getFilePath($theme, $fileName);

        if (!File::isFile($fullPath)) {
            return null;
        }

        if (($content = @File::get($fullPath)) === false) {
            return null;
        }

        $obj = new \stdClass();//new static($theme);
        $obj->fileName = $fileName;
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

    /**
     * Returns the absolute file path.
     * @param \Cms\Classes\Theme $theme Specifies a theme the file belongs to.
     * @param string$fileName Specifies the file name to return the path to.
     * @return string
     */
    protected static function getFilePath($plugin, $fileName)
    {
        return $theme->getPath().'/'.static::getObjectTypeDirName().'/'.$fileName;
    }

    protected function filterThePlugin($var)
    {
        $this->activePluginVector;
        return($var & 1);
    }
    protected function prepareComponentList($activePlugin)
    {
        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();
        $componentList = [];
        foreach ($plugins as $key=>$plugin) {
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
            }//foreach
        }//foreach

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
        $activePluginVector = $this->getActivePlugin();
        return ['#'.$this->getId('component-list') => $this->makePartial('items', ['items'=>$this->getData($activePluginVector)])];
    }

    public function refreshActivePlugin()
    {
        $activePluginVector = $this->getActivePlugin();
        return ['#'.$this->getId('body') => $this->makePartial('widget-contents', ['data'=>$this->getData($activePluginVector), 'pluginVector'=>$activePluginVector])];
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
}

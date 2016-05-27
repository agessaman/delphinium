<?php
/* Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
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
namespace Delphinium\Vanilla\Classes;

use URL;
use File;
use Yaml;
use Lang;
use Cache;
use Event;
use Config;
use DbDongle;
use SystemException;
use ApplicationException;
use System\Models\Parameters;
use Cms\Models\ThemeData;
use DirectoryIterator;

/**
 * This class represents the Plugin whose assets/content users will be editing in the CodeEditor widget.
 *
 * @package Delphinium\Vanilla
 * @author Damaris Zarco
 */
class Plugin
{
    /**
     * @var string Specifies the theme directory name.
     */
    protected $dirName;

    /**
     * @var mixed Keeps the cached configuration file values.
     */
    protected $configCache = null;

    /**
     * @var mixed Active theme cache in memory
     */
    protected static $activePluginCache = false;

    /**
     * @var mixed Edit theme cache in memory
     */
    protected static $editPluginCache = false;

//    const ACTIVE_KEY = 'cms::theme.active';
//    const EDIT_KEY = 'cms::theme.edit';

    const ACTIVE_KEY = 'vanilla::plugin.active';
    const EDIT_KEY = 'vanilla::plugin.edit';

    /**
     * Loads the theme.
     * @return self
     */
    public static function load($dirName)
    {
        $plugin = new static;
        $plugin->setDirName($dirName);
        return $plugin;
    }

    /**
     * Returns the absolute theme path.
     * @param  string $dirName Optional theme directory. Defaults to $this->getDirName()
     * @return string
     */
    public function getPath($dirName = null)
    {
        if (!$dirName) {
            $dirName = $this->getDirName();
        }

        return base_path().'/'.$dirName;
    }

    /**
     * Sets the theme directory name.
     * @return void
     */
    public function setDirName($dirName)
    {
        $this->dirName = $dirName;

    }

    /**
     * Returns the theme directory name.
     * @return string
     */
    public function getDirName()
    {
        return $this->dirName;
    }

    /**
     * Helper for {{ theme.id }} twig vars
     * Returns a unique string for this theme.
     * @return string
     */
    public function getId()
    {
        return snake_case(str_replace('/', '-', $this->getDirName()));
    }

    /**
     * Determines if a theme with given directory name exists
     * @param string $dirName The theme directory
     * @return bool
     */
    public static function exists($dirName)
    {
        $plugin = static::load($dirName);
        $path = $plugin->getPath();

        return File::isDirectory($path);
    }

    /**
     * Returns a list of pages in the theme.
     * This method is used internally in the routing process and in the back-end UI.
     * @param boolean $skipCache Indicates if the pages should be reloaded from the disk bypassing the cache.
     * @return array Returns an array of \Cms\Classes\Page objects.
     */
    public function listPages($skipCache = false)
    {
        return Page::listInTheme($this, $skipCache);
    }

    /**
     * Returns true if this theme is the chosen active theme.
     */
    public function isActiveTheme()
    {
        $activeTheme = self::getActiveTheme();
        return $activeTheme && $activeTheme->getDirName() == $this->getDirName();
    }

    /**
     * Returns the active theme.
     * By default the active theme is loaded from the cms.activeTheme parameter,
     * but this behavior can be overridden by the cms.activeTheme event listeners.
     * @return \Cms\Classes\Theme Returns the loaded theme object.
     * If the theme doesn't exist, returns null.
     */
    public static function getActiveTheme()
    {
        if (self::$activeThemeCache !== false) {
            return self::$activeThemeCache;
        }

        $activeTheme = Config::get('cms.activeTheme');

        if (DbDongle::hasDatabase()) {
            $dbResult = Cache::remember(self::ACTIVE_KEY, 1440, function() {
                return Parameters::applyKey(self::ACTIVE_KEY)->pluck('value');
            });

            if ($dbResult !== null && static::exists($dbResult)) {
                $activeTheme = $dbResult;
            }
        }

        $apiResult = Event::fire('cms.activeTheme', [], true);
        if ($apiResult !== null) {
            $activeTheme = $apiResult;
        }

        if (!strlen($activeTheme)) {
            throw new SystemException(Lang::get('cms::lang.theme.active.not_set'));
        }

        $plugin = static::load($activeTheme);

        if (!File::isDirectory($plugin->getPath())) {
            return self::$activeThemeCache = null;
        }

        return self::$activeThemeCache = $plugin;
    }

    /**
     * Sets the active theme.
     * The active theme code is stored in the database and overrides the configuration cms.activeTheme parameter.
     * @param string $code Specifies the  active theme code.
     */
    public static function setActiveTheme($code)
    {
        self::resetCache();
        Parameters::set(self::ACTIVE_KEY, $code);
    }

    /**
     * Returns the edit theme.
     * By default the edit theme is loaded from the cms.editTheme parameter,
     * but this behavior can be overridden by the cms.editTheme event listeners.
     * If the edit theme is not defined in the configuration file, the active theme
     * is returned.
     * @return \Cms\Classes\Theme Returns the loaded theme object.
     * If the theme doesn't exist, returns null.
     */
    public static function getEditTheme()
    {
        if (self::$editThemeCache !== false) {
            return self::$editThemeCache;
        }

        $editTheme = Config::get('cms.editTheme');
        if (!$editTheme) {
            $editTheme = static::getActiveTheme()->getDirName();
        }

        $apiResult = Event::fire('cms.editTheme', [], true);
        if ($apiResult !== null) {
            $editTheme = $apiResult;
        }
        if (!strlen($editTheme)) {
            throw new SystemException(Lang::get('cms::lang.theme.edit.not_set'));
        }

        $plugin = static::load($editTheme);
        if (!File::isDirectory($plugin->getPath())) {
            return self::$editThemeCache = null;
        }

        return self::$editThemeCache = $plugin;
    }

    /**
     * Returns a list of all themes.
     * @return array Returns an array of the Theme objects.
     */
    public static function all()
    {
        $it = new DirectoryIterator(themes_path());
        $it->rewind();

        $result = [];
        foreach ($it as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }

            $plugin = static::load($fileinfo->getFilename());

            $result[] = $plugin;
        }

        return $result;
    }

    /**
     * Reads the theme.yaml file and returns the theme configuration values.
     * @return array Returns the parsed configuration file values.
     */
    public function getConfig()
    {
        if ($this->configCache !== null) {
            return $this->configCache;
        }

        $path = $this->getPath().'/theme.yaml';
        if (!File::exists($path)) {
            return $this->configCache = [];
        }

        return $this->configCache = Yaml::parseFile($path);
    }

    /**
     * Returns a value from the theme configuration file by its name.
     * @param string $name Specifies the configuration parameter name.
     * @param mixed $default Specifies the default value to return in case if the parameter
     *                       doesn't exist in the configuration file.
     * @return mixed Returns the parameter value or a default value
     */
    public function getConfigValue($name, $default = null)
    {
        return array_get($this->getConfig(), $name, $default);
    }

    /**
     * Writes to the theme.yaml file with the supplied array values.
     * @param array $values Data to write
     * @param array $overwrite If true, undefined values are removed.
     * @return void
     */
    public function writeConfig($values = [], $overwrite = false)
    {
        if (!$overwrite) {
            $values = $values + (array) $this->getConfig();
        }

        $path = $this->getPath().'/theme.yaml';
        if (!File::exists($path)) {
            throw new ApplicationException('Path does not exist: '.$path);
        }

        $contents = Yaml::render($values);
        File::put($path, $contents);
        $this->configCache = $values;
    }

    /**
     * Returns the theme preview image URL.
     * If the image file doesn't exist returns the placeholder image URL.
     * @return string Returns the image URL.
     */
    public function getPreviewImageUrl()
    {
        $previewPath = '/assets/images/theme-preview.png';
        if (File::exists($this->getPath().$previewPath)) {
            return URL::asset('themes/'.$this->getDirName().$previewPath);
        }

        return URL::asset('modules/cms/assets/images/default-theme-preview.png');
    }

    /**
     * Resets any memory or cache involved with the active or edit theme.
     * @return void
     */
    public static function resetCache()
    {
        self::$activeThemeCache = false;
        self::$editThemeCache = false;
        Cache::forget(self::ACTIVE_KEY);
        Cache::forget(self::EDIT_KEY);
    }

    /**
     * Returns true if this theme has form fields that supply customization data.
     * @return bool
     */
    public function hasCustomData()
    {
        return $this->getConfigValue('form', false);
    }

    /**
     * Implements the getter functionality.
     * @param  string  $name
     * @return void
     */
    public function __get($name)
    {
        if ($this->hasCustomData()) {
            $plugin = ThemeData::forTheme($this);
            return $plugin->{$name};
        }

        return null;
    }

    /**
     * Determine if an attribute exists on the object.
     * @param  string  $key
     * @return void
     */
    public function __isset($key)
    {
        if ($this->hasCustomData()) {
            $plugin = ThemeData::forTheme($this);
            return isset($plugin->{$key});
        }

        return false;
    }

}

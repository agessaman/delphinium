<?php namespace Delphinium\Vanilla\Classes;

use Config;
use Delphinium\Vanilla\Classes\Plugin;

/**
 * The CMS theme asset file class.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Asset extends ContentObject
{
    /**
     * Creates an instance of the object and associates it with a CMS theme.
     * @param \Cms\Classes\Theme $plugin Specifies the theme the object belongs to.
     */
    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        self::$allowedExtensions = self::getEditableExtensions();
    }

    /**
     * Sets path for new asset files created from the back-end.
     * @param string $path Specifies the path.
     */
    public function setInitialPath($path)
    {
        $this->fileName = $path;
    }

    /**
     * Returns a list of editable asset extensions.
     * The list can be overridden with the cms.editableAssetTypes configuration option.
     * @return array
     */
    public static function getEditableExtensions()
    {
        $defaultTypes =  ['css','js','less','sass','scss'];

        $configTypes = Config::get('cms.editableAssetTypes');
        if (!$configTypes) {
            return $defaultTypes;
        }

        return $configTypes;
    }

    /**
     * Returns the directory name corresponding to the object type.
     * For pages the directory name is "pages", for layouts - "layouts", etc.
     * @return string
     */
    public static function getObjectTypeDirName()
    {
        return 'assets';
    }

    /**
     * {@inheritDoc}
     */
    protected static function getMaxAllowedPathNesting()
    {
        return null;
    }
}

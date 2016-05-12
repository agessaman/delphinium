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

use Config;
use Cms\Classes\Theme;
use Delphinium\Vanilla\Classes\PluginContentObject;
use Delphinium\Vanilla\Classes\Plugin;

/**
 * The CMS theme asset file class.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Asset extends PluginContentObject
{
    /**
     * Creates an instance of the object and associates it with a CMS theme.
     * @param \Cms\Classes\Theme $theme Specifies the theme the object belongs to.
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

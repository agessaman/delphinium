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

namespace Delphinium\Roots\Updatableobjects;

use \DateTime;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Roots\Enums\ModuleItemType;
use Delphinium\Roots\Enums\CompletionRequirementType;


class ModuleItem {
    public $title;
    public $type;
    public $content_id;
    public $page_url;
    public $external_url;
    public $completion_requirement_type;
    public $completion_requirement_min_score;
    public $published;
    public $position;
    public $tags;
    
    function getTags() {
        return $this->tags;
    }
//    function setTags($tags) {
//        $this->tags = $tags;
//    }

    function __construct($title = null, $module_item_type=null, $content_id = null, $page_url = null, $external_url = null, 
        $completion_requirement_type = null, $completion_requirement_min_score = null, $published = false, $position = 1,array $tags = null)
    {
        
        if (($title)&&(!is_string($title))) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Parameter must be a string");
        }
        
        if(($module_item_type)&&(!ModuleItemType::isValidValue($module_item_type)))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"module_item_type");
        }
        
        if(($content_id) && !is_integer($content_id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"content_id", "Parameter must be an integer");
        }
        
        if(($completion_requirement_type)&&(!CompletionRequirementType::isValidValue($completion_requirement_type)))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"completion_requirement_type");
        }
        
        if(($completion_requirement_min_score)&&(!is_integer($completion_requirement_min_score)))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"completion_requirement_min_score", "Parameter must be an integer");
        }
        
        
        if($tags)
        {
            
            if(!$content_id)
            {
                $this->tags = $tags;
                throw new InvalidParameterInRequestObjectException(get_class($this),"content_id", "In order to add tags a content id must be provided");
            }
            else
            {   
                $str = implode(", ", $tags);
                $this->tags=$str;
            }
        }
        else
        {
            $this->tags = $tags;
        }
        
        $this->title = $title;
        $this->type = $module_item_type;
        $this->content_id = $content_id;
        $this->page_url = $page_url;
        $this->external_url = $external_url;
        $this->completion_requirement_type = $completion_requirement_type;
        $this->completion_requirement_min_score = $completion_requirement_min_score;
        $this->position = $position;
        $this->published = ($published) ? 'true' : 'false';
        
    }
}
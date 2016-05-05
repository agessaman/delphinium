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

namespace Delphinium\Roots\Models;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class File 
{
    public $name;
    public $size;
    public $content_type;
    public $on_duplicate;
    
    function __construct($name, $size, $content_type, $on_duplicate)
    {
        if(!is_string($name))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Name must be a string");
        }
        
        if(!is_int($size))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"size", "Size must be an integer");
        }
        
        if(!is_string($content_type))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"content_type", "Content_type must be a string");
        }
        
        if(!is_string($on_duplicate))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"on_duplicate", "On_duplicate must be a string");
        }
        
        $this->name = $name;
        $this->size = $size;
        $this->content_type = $content_type;
        $this->on_duplicate = $on_duplicate;
    }
}
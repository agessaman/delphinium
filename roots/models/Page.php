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

use Delphinium\Roots\Enums\ModuleItemEnums\PageEditingRoles;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class Page
{   
    public $title;
    public $body;
    public $pageEditingRole;
    public $notifyOfUpdate;
    public $published;
    public $frontPage;
    
    function __construct($title, $body, $pageEditingRole,  $notifyOfUpdate, $published) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Parameter must be a string");
        }
        if(!PageEditingRoles::isValidValue($pageEditingRole))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"pageEditingRole", "Page Editing Role is not valid");
        }
        if(!is_bool($notifyOfUpdate))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"notifyOfUpdate", "Parameter must be a boolean");
        }
        if(!is_bool($published))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"published", "Parameter must be a boolean");
        }
        $this->title = $title;
        $this->body = $body;
        $this->pageEditingRole = $pageEditingRole;
        $this->notifyOfUpdate = ($notifyOfUpdate)?true:false;
        $this->published = ($published)?true:false;
    }
    
}
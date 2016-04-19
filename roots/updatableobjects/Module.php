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

class Module {
    public $name;
    public $unlock_at;
    public $prerequisite_module_ids;
      
    public $published;
    public $position;
    
    function getName() {
        return $this->name;
    }

    function getUnlock_at() {
        return $this->unlock_at;
    }

    function getPrerequisite_module_ids() {
        return $this->prerequisite_module_ids;
    }

    /**
     * 
     * @param type $name string - Name of this module
     * @param DateTime $utc_unlock_at Date - date in which this module will be unlocked. Must be in UTC time
     * @param array $prerequisite_module_ids Array - the ids of prerequisite modules
     * @param type $published boolean - whether the module should be published
     * @param type $position int - The position this module will take
     * @throws InvalidParameterInRequestObjectException If the parameters are incorrect
         */
    function __construct($name = null,  DateTime $utc_unlock_at = null, array $prerequisite_module_ids = null, $published = null, $position =1)
    {
        if (($name)&&(!is_string($name))) {
            throw new InvalidParameterInRequestObjectException(get_class($this),"name", "Parameter must be a string");
        }
        
        $current_date = new \DateTime("now");
        if (($utc_unlock_at)&&($current_date >  $utc_unlock_at))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"unlock_at", "Unlock at date must be in the future");
        }
        
        if(($prerequisite_module_ids)&&!is_array($prerequisite_module_ids))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"prerequisite_module_ids", "Parameter must be an array");
        }
        
        $this->name = $name;
        $this->unlock_at = ($utc_unlock_at) ? $utc_unlock_at->format("c") : null;
        $this->prerequisite_module_ids = $prerequisite_module_ids;
        $this->position = $position;
        
        if(!is_null($published))
        {
            $this->published = ($published) ? 'true' : 'false';
        }
    }
}
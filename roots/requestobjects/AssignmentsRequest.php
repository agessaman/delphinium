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

namespace Delphinium\Roots\Requestobjects;

use Delphinium\Roots\Enums\Lms;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Roots\Models\Assignment;

class AssignmentsRequest extends RootsRequest
{
    private $assignment_id;
    private $fresh_data;
    private $include_tags;
    public $assignment;
    
        function getAssignment_id() {
        return $this->assignment_id;
    }
    
    function getFresh_data() {
        return $this->fresh_data;
    }
    
    function setFresh_data($fresh_data) {
        $this->fresh_data = $fresh_data;
    }
 
    function getIncludeTags() {
        return $this->include_tags;
    }

    function setIncludeTags($include_tags) {
        $this->include_tags = $include_tags;
    }

    function getAssignment() {
        return $this->assignment;
    }    
    
    function __construct($actionType, $assignment_id = null, $fresh_data = false, Assignment $assignment = null, $include_tags = false) 
    {
        //this takes care of setting the lms and the ActionType in the parent class (RootsRequest)
        parent::__construct($actionType);

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;
        }
        else
        {
            throw new \Exception("Invalid LMS"); 
        }
        
        if($assignment_id && !is_integer($assignment_id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"assignment_id", "Parameter must be an integer");
        }
        
        $this->assignment_id = $assignment_id;
        $this->fresh_data = $fresh_data;
        $this->include_tags = $include_tags;
        $this->assignment = $assignment;
    }
}
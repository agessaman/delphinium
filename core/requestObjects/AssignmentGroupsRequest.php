<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;

class AssignmentGroupsRequest extends RootsRequest
{
    private $assignment_group_id;
    private $include_assignments;
    
    function getAssignment_group_id() {
        return $this->assignment_group_id;
    }

    function getInclude_assignments() {
        return $this->include_assignments;
    }
        
    function __construct($actionType, $include_assignments, $assignment_group_id = null) 
    {
        if(ActionType::isValidValue($actionType))
        {  
            $this->actionType = $actionType;
        }
        else
        {
            throw new \Exception("Invalid ActionType"); 
        }

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;
        }
        else
        {
            throw new \Exception("Invalid LMS"); 
        }
        
        if($assignment_group_id && !is_integer($assignment_group_id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"assignment_group_id", "Parameter must be an integer");
        }
        
        $this->assignment_group_id = $assignment_group_id;
        $this->include_assignments = $include_assignments;
    }
}
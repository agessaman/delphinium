<?php namespace Delphinium\Roots\RequestObjects;

use Delphinium\Roots\Enums\CommonEnums\Lms;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class AssignmentGroupsRequest extends RootsRequest
{
    private $assignment_group_id;
    private $include_assignments;
    private $fresh_data;
    
    function getAssignment_group_id() {
        return $this->assignment_group_id;
    }

    function getInclude_assignments() {
        return $this->include_assignments;
    }
    
    function getFresh_data() {
        return $this->fresh_data;
    }

    function __construct($actionType, $include_assignments, $assignment_group_id = null,  $fresh_data = false) 
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
        
        if($assignment_group_id && !is_integer($assignment_group_id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"assignment_group_id", "Parameter must be an integer");
        }
        
        $this->assignment_group_id = $assignment_group_id;
        $this->include_assignments = $include_assignments;
        $this->fresh_data = $fresh_data;
    }
}
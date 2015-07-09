<?php namespace Delphinium\Roots\RequestObjects;

use Delphinium\Roots\Enums\CommonEnums\Lms;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Roots\Models\Assignment;

class AssignmentsRequest extends RootsRequest
{
    private $assignment_id;
    private $fresh_data;
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
 
    function getAssignment() {
        return $this->assignment;
    }    
    
    function __construct($actionType, $assignment_id = null, $fresh_data = false, Assignment $assignment = null) 
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
        $this->assignment = $assignment;
    }
}
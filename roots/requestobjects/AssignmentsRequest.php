<?php namespace Delphinium\Roots\Requestobjects;

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
<?php namespace Delphinium\Roots\Requestobjects;

use Delphinium\Roots\Enums\Lms;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class QuizRequest extends RootsRequest
{
    private $id;
    private $fresh_data;
    
    function getId() {
        return $this->id;
    }

    function getFresh_data() {
        return $this->fresh_data;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFresh_data($fresh_data) {
        $this->fresh_data = $fresh_data;
    }

    function __construct($actionType, $id = null, $fresh_data = false)
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
        
        if($id && !is_integer($id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"id", "Parameter must be an integer");
        }
        
        $this->id = $id;
        $this->fresh_data = $fresh_data;
    }
}
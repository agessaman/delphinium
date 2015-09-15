<?php namespace Delphinium\Roots\Requestobjects;

use Delphinium\Roots\Enums\Lms;
use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class QuizRequest extends RootsRequest
{
    private $quiz_id;
    private $fresh_data;
    private $include_questions;
    
    function getInclude_questions() {
        return $this->include_questions;
    }

    function setInclude_questions($include_questions) {
        $this->include_questions = $include_questions;
    }

    function getId() {
        return $this->quiz_id;
    }

    function getFresh_data() {
        return $this->fresh_data;
    }

    function setId($id) {
        $this->quiz_id = $id;
    }

    function setFresh_data($fresh_data) {
        $this->fresh_data = $fresh_data;
    }

    function __construct($actionType, $quiz_id = null, $fresh_data = false, $include_questions = false)
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
        
        if($quiz_id && !is_integer($quiz_id))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"id", "Parameter must be an integer");
        }
        
        $this->quiz_id = $quiz_id;
        $this->fresh_data = $fresh_data;
        $this->include_questions = $include_questions;
    }
}
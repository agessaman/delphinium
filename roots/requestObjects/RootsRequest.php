<?php namespace Delphinium\Roots\RequestObjects;


use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Delphinium\Roots\Enums\CommonEnums\Lms;

abstract class RootsRequest
{
    private $actionType;
    private $lms;
    
    function getActionType() {
        return $this->actionType;
    }

    function getLms() {
        return $this->lms;
    }

    function __construct($actionType) 
    {
        if(ActionType::isValidValue($actionType))
        {  
            $this->actionType = $actionType;
        }
        else
        {
            throw new \Exception("Invalid ActionType"); 
        }
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
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
    }
}
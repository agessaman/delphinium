<?php namespace Delphinium\Core\RequestObjects;


use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

abstract class RootsRequest
{
    public $actionType;
    public $lms;
    
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
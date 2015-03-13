<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class AssignmentsRequest extends RootsRequest
{
    
    function __construct($actionType) 
    {
        if(ActionType::isValidValue($actionType))
        {  
            $this->actionType = $actionType;
        }

        $lms = strtoupper($_SESSION['lms']);
        if(Lms::isValidValue($lms))
        {
            $this->lms = $lms;
        }
    }
}
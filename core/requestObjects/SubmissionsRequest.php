<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class SubmissionsRequest extends RootsRequest
{
    /*
     * Properties
     */
    
    //TODO: generate getters & setters
    public $actionType;
    public $studentIds;
    public $assignmentIds;
    public $lms;
    
    /*
     * Constructor 
     * Will set default params
     */
    function __construct($actionType = ActionType::GET, $studentIds = array(), $assignmentIds = array(), $lms = Lms::Canvas) 
    {
        
    }
}
<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class SubmissionsRequest extends RootsRequest
{
    /*
     * Properties
     */
    
    //TODO: generate getters & setters
    public $studentIds;
    public $assignmentIds;
    public $multipleUsers;
    public $multipleAssignments;
    
    /*
     * Constructor 
     * Will set default params
     */
    function __construct($actionType = ActionType::GET, $studentIds = null, $assignmentIds = null, $lms = Lms::Canvas, 
            $multipleUsers = false, $multipleAssignments = false) 
    {
        
    }
}
<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;

class SubmissionsRequest extends RootsRequest
{
    /*
     * Properties
     */
    public $allStudents;
    public $studentIds;
    public $assignmentIds;
    
    /*
     * Constructor
     */
    function __construct() {
        $this->action = ActionType::GET;
        $this->allStudents = false;
    }
}
<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;

class SubmissionsRequest extends RootsRequest
{
    /*
     * Properties
     */
    
    
    public $studentIds;
    public $assignmentIds;
    public $multipleUsers;
    public $multipleAssignments;
    
    function getStudentIds() {
        return $this->studentIds;
    }

    function getAssignmentIds() {
        return $this->assignmentIds;
    }

    function getMultipleUsers() {
        return $this->multipleUsers;
    }

    function getMultipleAssignments() {
        return $this->multipleAssignments;
    }

    function setStudentIds($studentIds) {
        $this->studentIds = $studentIds;
    }

    function setAssignmentIds($assignmentIds) {
        $this->assignmentIds = $assignmentIds;
    }

    function setMultipleUsers($multipleUsers) {
        $this->multipleUsers = $multipleUsers;
    }

    function setMultipleAssignments($multipleAssignments) {
        $this->multipleAssignments = $multipleAssignments;
    }

    
    
    /*
     * Constructor 
     */
    function __construct($actionType, $lms, $studentIds = null, $assignmentIds = null, 
            $multipleUsers = false, $multipleAssignments = false) 
    {
        $this->actionType = $actionType;
        $this->lms = $lms;
        $this->setAssignmentIds($assignmentIds);
        $this->setMultipleAssignments($multipleAssignments);
        $this->setMultipleUsers($multipleUsers);
        $this->setStudentIds($studentIds);
    }
}
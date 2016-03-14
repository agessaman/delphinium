<?php namespace Delphinium\Roots\Requestobjects;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class SubmissionsRequest extends RootsRequest
{
    /*
     * Properties
     */
    private $studentIds;
    private $allStudents;
    private $multipleStudents;
    
    private $assignmentIds;
    private $allAssignments;
    private $multipleAssignments;
    private $grouped;
    
    private $includeTags;
    
    function getAllAssignments() {
        return $this->allAssignments;
    }

    function getAllStudents() {
        return $this->allStudents;
    }

    function getStudentIds() {
        return $this->studentIds;
    }

    function getAssignmentIds() {
        return $this->assignmentIds;
    }

    function getMultipleStudents() {
        return $this->multipleStudents;
    }

    function getMultipleAssignments() {
        return $this->multipleAssignments;
    }

    
    function getIncludeTags() {
        return $this->includeTags;
    }

    function setIncludeTags($includeTags) {
        $this->includeTags = $includeTags;
    }

    
    function getGrouped() {
        return $this->grouped;
    }

    function setGrouped($grouped) {
        $this->grouped = $grouped;
    }

    /**
     * SubmissionsRequest constructor.
     * @param $actionType An http verb from the ActionType enum
     * @param array|null $studentIds An array with student ids; if empty, all students will be retrieved
     * @param bool $allStudents Whether to return all arrays
     * @param array $assignmentIds An array of assignment ids
     * @param bool $allAssignments Whether to return all assignments
     * @param bool $multipleStudents Whether to return more than one student
     * @param bool $multipleAssignments Whether to return more than one assignment
     * @param bool $includeTags Whether to return tags
     * @param bool $grouped Whether to return the submissions grouped by student
     */
    function __construct($actionType, array $studentIds = null, $allStudents = false, array $assignmentIds = array(), $allAssignments = false, 
            $multipleStudents = false, $multipleAssignments = false, $includeTags = false, $grouped = false) 
    {
        //this takes care of setting the lms and the ActionType in the parent class (RootsRequest)
        parent::__construct($actionType);
        
        //validate assignments
        if($multipleAssignments && (($assignmentIds===null)||(count($assignmentIds)<2)))
        {
            if(!$allAssignments)
            {
                throw new InvalidParameterInRequestObjectException(get_class($this),"assignmentIds", 
                    "Must provide at least two assignmentIds, or param allAssignments must be true in order to return multiple assignments");
            }
        }
        
        if(!$multipleAssignments&&(count($assignmentIds)>1))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"assignmentIds", 
                    "Parameter has too many assignmentIds (param multipleAssignments is set to false)");
        }
        
        if(!$multipleAssignments&&($allAssignments))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"allAssignments", 
                    "Parameter cannot be true because param multipleAssignments is set to false");
        }
        
        
        //validate students
        if ($multipleStudents && (($studentIds === null || count($studentIds)<2 )))
        {
            if(!$allStudents)
           {
               throw new InvalidParameterInRequestObjectException(get_class($this), "studentIds", 
                  "Must provide at least two studentIds, or param allStudents must be true in order to return multiple users. ");
           }
        }
        
        if(!$multipleStudents &&(count($studentIds)>1))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"studentIds", 
                    "Parameter has too many student Ids (param multipleUsers is set to false)");
        }
        
        if(!$multipleStudents &&($allStudents))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"allStudents", 
                    "Parameter cannot be true because param multipleUsers is set to false");   
        }
        
        $this->assignmentIds = $assignmentIds;
        $this->allStudents = $allStudents;
        $this->multipleAssignments=$multipleAssignments;
        $this->multipleStudents=$multipleStudents;
        $this->studentIds=$studentIds;
        $this->allAssignments = $allAssignments;
        $this->includeTags = $includeTags;
        $this->grouped = $grouped;
    }
    
    
}
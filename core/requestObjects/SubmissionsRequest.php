<?php namespace Delphinium\Core\RequestObjects;

use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;

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

    /*
     * Constructor 
     */
    function __construct($actionType, array $studentIds = null, $allStudents = false, array $assignmentIds = null, $allAssignments = false, 
            $multipleStudents = false, $multipleAssignments = false) 
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
    }
    
    
}
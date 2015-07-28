<?php namespace Delphinium\Iris\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Roots\RequestObjects\SubmissionsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;


class RestApi extends Controller 
{
    public function getModuleStates()
    {
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = false;
        $includeContentItems = false;
        $module = null;
        $moduleItem = null;
        $freshData = true;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $roots = new Roots();
        $res = $roots->getModuleStates($req);
        return $res;
    }

    public function getStudentSubmissions()
    {
        $studentId = \Input::get('studentId');
        
        $studentIds = array($studentId);
        $assignmentIds = array();//if we leave this param empty it will return all of the available submissions 
        //(see https://canvas.instructure.com/doc/api/submissions.html#method.submissions_api.for_students)
        $multipleStudents = false;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = true;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        return $res;
    }
    
}

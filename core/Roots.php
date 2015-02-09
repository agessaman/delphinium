<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;

class Roots
{
    /*
     * Public Functions
     */
    public function submissions(SubmissionsRequest $request)
    {
        $result;
        switch ($request->lms)
        {
            case (Lms::Canvas):
                $result = $this->canvasSubmissions($request);
                break;
            default:
                $result = $this->canvasSubmissions($request);
                break;
                
        }
            
        return $result;
    }
    
    public function assignments(AssignmentsRequest $request)
    {
        return $this->parseAssignments($request);
    }
    
    public function modules(ModulesRequest $request)
    {
        return $this->parseModules($request);
    }
    
    
    
    /*
     * Private Functions
     */
    private function canvasSubmissions(SubmissionsRequest $request)
    {
        $userId = $_SESSION['userID'];
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        
        $url = $domain."/api/v1/courses/".$courseId."/";
       
        if(count($request->studentIds)>0 && count($request->assignmentIds)>0)
        {
            $url .= "students/submissions/?students_ids[]=".$request->studentIds."&assignment_ids[]=".$request->assignmentIds;
        }
        
        return $url;
        
    }
    
    private function parseAssignments(AssignmentsRequest $request)
    {
        echo "in assignments function from roots";
    }
    
    private function parseModules(ModulesRequest $request)
    {
        echo "in modules function from roots";
    }
}

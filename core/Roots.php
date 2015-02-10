<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Exceptions\RequestObjectException;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;

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
        
        $result;
        switch ($request->lms)
        {
            case (Lms::Canvas):
                $result = $this->canvasModules($request);
                break;
            default:
                $result = $this->canvasModules($request);
                break;
                
        }
            
        return $result;
    }
    
    
    
    /*
     * Private Functions
     */
    
    private function callLmsApi()
    {
        
    }
    
    private function canvasSubmissions(SubmissionsRequest $request)
    {
        $userId = $_SESSION['userID'];
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";
       
        //For some reason the submissions API is funky when it comes to multipleAssignments. In this case
        //it doesn't follow the same pattern as the rest of the API calls
        if($request->multipleAssignments)
        {
            $urlPieces[]="students/submissions";
            
            //STUDENT IDS
            //student_ids can be "All", or a list of actual studentIds
            if((count($request->studentIds)===1) && strtolower($request->studentIds[0])==='all')
            {
                $urlArgs[]="student_ids[]=all";
            }
            else
            {
                try{
                    $studentIds = implode(",", (array)$request->studentIds);
                    $urlArgs[]="student_ids[]={$studentIds}";
                }
                catch(Exception $e)
                {
                    throw new InvalidParameterInRequestObjectException(get_class($request),"studentIds");
                }
            }
            
            //ASSIGNMENT IDS
            //assignment_ids can be a list of assignmentIds, or if empty, all assignments will be returned
            if(count($request->assignmentIds)>0)
            {
                try
                {
                    $assignmentIds = implode(",", $request->assignmentIds);
                    $urlArgs[]= "assignment_ids[]={$assignmentIds}";
                }
                catch(Exception $e)
                {
                    throw new InvalidParameterInRequestObjectException(get_class($request),"assignmentIds", $e->getMessage());
                }
            }
            
        }
        else if($request->multipleUsers)
        {
            $urlPieces[]= "assignments"; //input1
            if(count($request->assignmentIds)!==1)
            {
                throw new InvalidParameterInRequestObjectException(get_class($request),"assignmentIds");
            }
            else
            {
                $urlPieces[]= implode(",",(array)$request->assignmentIds); //input2
            }
            
            if(count($request->studentIds)===1)
            {
                $urlPieces[]= "submissions"; //input3
            }
            else
            {
                throw new InvalidParameterInRequestObjectException(get_class($request),"studentIds");
            }
        }
        else
        {
            $urlPieces[]= "assignments"; //input1
            if(count($request->assignmentIds)>0)
            {
                $urlPieces[]= implode(",",(array)$request->assignmentIds); //input2
            }
            
            $urlPieces[]= "submissions"; //input3
            if(count($request->studentIds)>0)
            {
                $urlPieces[]= implode(",", (array)$request->studentIds); //input4
            }
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";
        
        $url = $this->constructUrl($urlPieces, $urlArgs);
        
        return $url;
    }
    
    private function parseAssignments(AssignmentsRequest $request)
    {
        echo "in assignments function from roots";
    }
    
    private function canvasModules(ModulesRequest $request)
    {
        $userId = $_SESSION['userID'];
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";
       
        $urlPieces[] = "modules";
        if($request->moduleId)
        {
            $urlPieces[]=$request->moduleId;
            if($request->includeContentItems)
            {
                $urlPieces[]= "items";
            }
            if($request->contentId)
            {
                $urlPieces[]= $request->contentId;
            }

        }
        else
        {//if not moduleId, they must want all the modules
            if($request->includeContentItems)
            {
                $urlArgs[] = "include[]=items";
            }
            if($request->includeContentDetails)
            {
                $urlArgs[] = "include[]=content_details";
            }
        }
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";
        
        $url = $this->constructUrl($urlPieces, $urlArgs);
        
        return $url;
        
    }
    
    
    private function constructUrl($urlPieces, $urlArgs = null)
    {
        $urlStr = "";
        for($i = 0;$i<=count($urlPieces)-1;$i++)
        {
            $urlStr.= $urlPieces[$i]."/";
            if($i===count($urlPieces)-1)
            {
                //we've reached the last url piece. Attach ? for params
                $urlStr.="?";
            }
        }
        
        if($urlArgs)
        {
            $urlParamsStr = "";
            for($i = 0;$i<=count($urlArgs)-1;$i++)
            {
                $urlParamsStr.= $urlArgs[$i];
                if($i<count($urlArgs)-1)
                {
                    $urlParamsStr.= "&";
                }
            }
        }
     
        $url = $urlStr.$urlParamsStr;
        return $url;
    }
}

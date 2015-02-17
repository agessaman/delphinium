<?php namespace Delphinium\Core;

use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\Lms;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Exceptions\RequestObjectException;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Core\lmsClasses\Canvas;
use Delphinium\Raspberry\Models\Module;
use GuzzleHttp\Client;

class Roots
{
    private $useCachedData = true;
    private $cacheTime = 0;
    private $forever = false;
    /*
     * @useCachedData = if true will return cached data. If false will return "fresh" data
     * @cacheTime = number of minutes to cache data for. If the value -1 is used, data will be cached "forever"
     */
    function __construct($useCachedData, $cacheTime) 
    {
        $this->useCachedData = $useCachedData;
        if($cacheTime <0)
        {
            $this->forever = true;
        }
        else
        {
            $this->cacheTime = $cacheTime;
        }
    }
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
        return true;
    }
    
    public function modules(ModulesRequest $request)
    {
        if($this->useCachedData)
        {
            return $this->getModuleData($request);
        }
        else
        {
            $result;
            switch ($request->lms)
            {
                case (Lms::CANVAS):
                    $result = $this->canvasModules($request);
                    break;
                default:
                    $result = $this->canvasModules($request);
                    break;

            }

            return $result;
        }
    }
    
    
    
    /*
     * Private Functions
     */
    
    private function getModuleData(ModulesRequest $request)
    {
        $courseId = $_SESSION['courseID'];
        
        
        $query = Module::query();
        $query->where('courseId','=',$courseId);
        
        if($request->moduleId)
        {
            $query->where('moduleId','=',$request->moduleId);
        }
        if($request->contentId)
        {
            $query->where('contentId','=', $request->contentId);
        }
        $results = $query->get();
        
        return $results;
        
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
        
        $response = $this->makeGuzzleRequest($request, $url);
        return $response->getBody();
    }
    
    private function canvasAssignments(AssignmentsRequest $request)
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
        
        $response = $this->makeGuzzleRequest($request, $url);
        
        $canvas = new Canvas($this->forever, $this->cacheTime);
        $items = $canvas->processModuleData(json_decode($response->getBody()), $courseId);
        return $items;
        
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
    
    private function makeGuzzleRequest($request, $url)
    {
        $client = new Client();
        switch($request->actionType)
        {
            case ActionType::GET:
                $response = $client->get($url);
                break;
            case ActionType::DELETE:
                $response = $client->delete($url);
                break;
            case ActionType::PUT:
                $response = $client->put($url);
                break;
            case ActionType::POST:
                $response = $client->post($url);
                break;
            default:
                $response = $client->get($url);
        }
        
        return $response;
    }
    
   
    
    
    
    
}

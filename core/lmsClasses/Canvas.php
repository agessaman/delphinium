<?php namespace Delphinium\Core\lmsClasses;

use Delphinium\Raspberry\Models\Module;
use Delphinium\Raspberry\Models\ModuleItem;
use Delphinium\Raspberry\Models\Content;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Guzzle\GuzzleHelper;
use Delphinium\Core\Models\CacheSetting;
use Illuminate\Support\Facades\Cache;

class Canvas
{
    private $useCachedData = true;//whether to use cached data
    private $forever = false;//whether the data should be cached forever
    private $cacheTime = 0;//or for certain amount of time (in minutes)
        
    /*
     * constructor
     */
    function __construct($dataType) 
    {
        $cacheSetting = CacheSetting::where('data_type', '=', $dataType)->first();
        
        if($cacheSetting->time<0)
        {
            $this->useCachedData = true;
            $this->forever = true;
        }
        else if($cacheSetting->time===0)
        {
            $this->useCachedData = false;
        }
        else
        {
            $this->useCachedData = true;
            $this->forever = false;
            $this->cacheTime = $cacheSetting->time;
        }
        
        
    }
    
    /*
     * public functions
     */
    public function processModuleRequest (ModulesRequest $request)
    {
        if($this->useCachedData)
        {
            return $this->searchModuleDataInCache($request);
        }
        else
        {
            return $this->getModuleDataFromCanvas($request);
        }
        
        
    }
    
    private function getModuleDataFromCanvas($request)
    {
        echo "get data from Canvas";
        //As per Jared's & Damaris' discussion when users request fresh module data we wil retrieve ALL module data so we can store it in 
        //cache and then we'll only return the data they asked for

        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];

        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = 'modules';
        $urlArgs[] = 'include[]=items';
        $urlArgs[]= 'include[]=content_details';

        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

        $response = GuzzleHelper::makeRequest($request, $url);

        return $this->processCanvasModuleData(json_decode($response->getBody()), $courseId);
    }
    
    public function processSubmissionsRequest(SubmissionsRequest $request)
    {
        /*
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
        
        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::makeRequest($request, $url);
        return $response->getBody();
        */
    }
    
    private function processAssignmentsRequest(AssignmentsRequest $request)
    {
        echo "in assignments function from roots";
    }
    
    public function processCanvasModuleData($data, $courseId)
    {
        $items = array();
        $moduleIdsArray = array();
        
        foreach($data as $moduleRow)
        {
             //we'll create an array with all the moduleIds that belong to this courseId
            $moduleIdsArray[] = $moduleRow->id;
            $module = $this->processSingleModule($moduleRow, $courseId);
            $items[] = $module;
        }

        //put in Cache a list of all the module keys for this course
        $moduleIdsKey = "{$courseId}-moduleIds";
        if(Cache::has($moduleIdsKey))
        {
            Cache::forget($moduleIdsKey);
        }
        if($this->forever)
        {
            Cache::forever($moduleIdsKey, $moduleIdsArray);
        }
        else
        {
            Cache::put($moduleIdsKey, $moduleIdsArray, $this->cacheTime);
        }

        return $items;
    }
    
    private function processSingleModule($moduleRow, $courseId)
    {
        //check if module exists
        $module = Module::firstOrNew(array('moduleId' => $moduleRow->id));//('moduleId','=',$module->id);
        $module->moduleId = $moduleRow->id;
        $module->courseId = $courseId;//do we need this?
        $module->name = $moduleRow->name;
        $module->position = $moduleRow->position;
        $module->unlock_at = $moduleRow->unlock_at;
        $module->require_sequential_progress = $moduleRow->require_sequential_progress;
        $module->publish_final_grade = $moduleRow->publish_final_grade;
        $module->prerequisite_module_ids = implode(",",$moduleRow->prerequisite_module_ids);
        $module->items_count = $moduleRow->items_count;
        if(isset($moduleRow->published)){$module->published = $moduleRow->published;}
        if(isset($moduleRow->state)){$module->state = $moduleRow->state;}

        
        if(isset($moduleRow->items)){
            //save moduleItems
            $moduleItems = $this->saveModuleItems($moduleRow->items, $courseId, $moduleRow->id);
            $module->moduleItems = $moduleItems;
        }
        $module->save();

//        $key = 'module'.$courseId."-".$module->moduleId;
        $key = "{$courseId}-module-{$module->moduleId}";
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {//toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!
            Cache::forever($key, $module->toArray());
        }
        else
        {
            Cache::put($key, $module->toArray(), $this->cacheTime);
        }

        
        return $module;
    }
    
    private function saveModuleItems($moduleItems, $courseId, $moduleId)
    {
        $key = '';
        $allItems = array();
        
        foreach($moduleItems as $mItem){
            $module = ModuleItem::firstOrNew(array(
                'module_id' => $moduleId,
                'module_item_id'=> $mItem->id
            ));
            $module->module_item_id = $mItem->id;
            $module->module_id = $mItem->module_id;
            $module->course_id = $courseId;
            $module->position = $mItem->position;
            $module->title = $mItem->title;
            $module->indent = $mItem->indent;
            $module->type = $mItem->type;
            
            if(isset($mItem->published)){$module->published = $mItem->published;}
            
            //if we don't have contentId we'll use the module_item_id. This is for tagging purposes
            $contentId = 0;
            if(isset($mItem->content_id))
            {
                $module->content_id = $mItem->content_id;
            }
            else
            {
                $module->content_id = $mItem->id;
            }
            
            if(isset($mItem->html_url)){$module->html_url = $mItem->html_url;}
            if(isset($mItem->url)){$module->url = $mItem->url;}
            if(isset($mItem->page_url)){$module->page_url = $mItem->page_url;}
            if(isset($mItem->external_url)){$module->external_url = $mItem->external_url;}
            if(isset($mItem->new_tab)){$module->new_tab = $mItem->new_tab;}
            if(isset($mItem->completion_requirement)){$module->completion_requirement = json_encode($mItem->completion_requirement);}
            if(isset($mItem->content_details) && isset($mItem->type))
            {
                $content = $this->saveContentDetails($courseId, $moduleId, $mItem->id, $module->content_id, $mItem->type,$mItem->content_details);
                $module->content = $content;
            }
            
            $module->save();
            array_push($allItems, $module);
            
            $key = "{$courseId}-module-{$moduleId}-moduleItem-{$mItem->id}";
//            $key="moduleItem-".$courseId.'-'.$moduleId.'-'.$mItem->id;
            
            if(Cache::has($key))
            {
                Cache::forget($key);
            }
            if($this->forever)
            {
                Cache::forever($key, $module->toArray());
            }
            else
            {
                Cache::put($key, $module->toArray(),$this->cacheTime);
            }
        }
        
        return $allItems;

    }
    
    private function saveContentDetails($courseId, $moduleId, $itemId, $contentId, $type, $contentDetails)
    {
//        $key="content-".$courseId.'-'.$moduleId.'-'.$itemId.'-'.$contentId;
        $key = "{$courseId}-module-{$moduleId}-moduleItem-{$itemId}-content-{$contentId}";
        
        $content = Content::firstOrNew(array('content_id'=>$contentId));

        $content->content_id= $contentId;
        $content->content_type= $type;
        $content->module_item_id = $itemId;
        //$content->tags=$contentDetails->content_id;
        if(isset($contentDetails->points_possible)){$content->points_possible= $contentDetails->points_possible;}
        if(isset($contentDetails->due_at)){$content->due_at= $contentDetails->due_at;}
        if(isset($contentDetails->unlock_at)){$content->unlock_at= $contentDetails->unlock_at;}
        if(isset($contentDetails->lock_at)){$content->lock_at= $contentDetails->lock_at;}
        if(isset($contentDetails->lock_explanation)){$content->lock_explanation= $contentDetails->lock_explanation;}
                
        $content->save();
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {
            Cache::forever($key, $content->toArray());
        }
        else
        {
            Cache::put($key, $content->toArray(), $this->cacheTime);
        }
        return $content;
    }
    
    private function searchModuleDataInCache(ModulesRequest $request)
    {
        $courseId = $_SESSION['courseID'];
        $key = "";
        if($request->moduleId)
        {
            if($request->moduleItemId)
            {
                $key = "{$courseId}-module-{$request->moduleId}-moduleItem-{$request->moduleItemId}";
            }
            else
            {
                $key = "{$courseId}-module-{$request->moduleId}";
            }
        }
        else
        {//if no moduleId was found they must want all the modules
            $items = array();
            $moduleIdsKey = "{$courseId}-moduleIds";
            $moduleIds = array();
            if(Cache::has($moduleIdsKey))
            {
                $moduleIds = Cache::get($moduleIdsKey);
                
                foreach($moduleIds as $id)
                {
                    $key = "{$courseId}-module-{$id}";
                    if (Cache::has($key))
                    {
                        echo "return cached data";
                        $value = Cache::get($key);
                        array_push($items,$value);
                    }
                    else
                    {
                        $this->getModuleDataFromCanvas($request);
                        return $this->searchModuleDataInCache($request);
                    }
                }
                return $items;
            }
            
            
        }
        
        
        if(Cache::has($key))
        {
            echo "return cached Data";
            $data = Cache::get($key);
            return $data;
        }
        else
        {
            $this->getModuleDataFromCanvas($request);
            return $this->searchModuleDataInCache($request);
        }
            
    }
}
<?php namespace Delphinium\Core\lmsClasses;

use \DateTime;
use Delphinium\Core\Cache\CacheHelper;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Core\Guzzle\GuzzleHelper;
use Delphinium\Core\Models\CacheSetting;
use Delphinium\Core\Models\ModuleItem;
use Delphinium\Core\Models\Content;
use Delphinium\Core\Models\Module;
use Delphinium\Core\Models\Assignment;
use Delphinium\Core\Models\AssignmentGroup;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\UpdatableObjects\UpdatableModule;
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
     
        /*NOTE: 
        * if time = -1, data will be cached forever
        * if time = 0, data will NOT be cached
        * if time >0, data will be cached for that many minutes
        */
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
    /*
     * MODULES
     */
    public function getModuleData(ModulesRequest $request)
    {
        //As per Jared's & Damaris' discussion when users request fresh module data we wil retrieve ALL module data so we can store it in 
        //cache and then we'll only return the data they asked for
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
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
    
    public function putModuleData(ModulesRequest $request)
    {
        if(!$request->moduleId)
        {
            throw new InvalidParameterInRequestObjectException(get_class($request),"moduleId", "Parameter is required");
        }
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = "modules/{$request->moduleId}";
        
        if($request->moduleItemId)
        {
            $urlPieces[] = "items/{$request->moduleItemId}";
            $scope = "module_item";
        }
        
        foreach($request->params as $key=>$value)
        {
            $urlArgs[] = "{$scope}[{$key}]={$value}";
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

        $response = GuzzleHelper::makeRequest($request, $url);
        
        //update cache if request was successful
        if ($response->getStatusCode() ==="200")
        {
            $newlyUpdated= \GuzzleHttp\json_decode($response->getBody());
            if(isset($newlyUpdated->module_id))
            {
                //it's a module item
                $this->updateModuleItemInCache($newlyUpdated);
            }
            else 
            {
                //it's a module
                $this->updateModuleInCache($newlyUpdated);
            }
        }
    }
    
    public function deleteModuleData(ModulesRequest $request)
    {
        $isModuleItem = false;
        if(!$request->moduleId)
        {
            throw new InvalidParameterInRequestObjectException(get_class($request),"moduleId", "Parameter is required");
        }
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $urlPieces[] = "modules/{$request->moduleId}";
        
        if($request->moduleItemId)
        {
            $isModuleItem = true;
            $urlPieces[] = "items/{$request->moduleItemId}";
            $scope = "module_item";
        }
        
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

        try
        {
            //delete from Canvas
            $response = GuzzleHelper::makeRequest($request, $url);
            
            //delete from cache & from DB
            $cacheHelper = new CacheHelper();
            
            /*
             * NOTE:
             * Cascading delete is not yet supported in OctoberCMS, so we have to do all the cascading deletes manually. 
             * See https://github.com/octobercms/october/issues/419
             */
            if($isModuleItem)
            {//delete the module item and all its contents
                $mItemkey = "{$courseId}-module-{$request->moduleId}-moduleItem-{$request->moduleItemId}";
                
                //we can't just delete the module straight from cache cause we need to delete it from the module (in cache), etc
                $cacheHelper->deleteModuleItemFromCache($mItemkey, $this->cacheTime);
                ModuleItem::where('module_item_id', '=', $request->moduleItemId)
                        ->where('module_id','=',$request->moduleId)->delete();
                
                $this->deleteModuleItemsContent($courseId, $request->moduleId, $request->moduleItemId);
                
            }
            else
            {//delete module, module items, and contents
                $moduleKey = "{$courseId}-module-{$request->moduleId}";
                $cacheHelper->deleteObjFromCache($moduleKey);
                
                Module::where('course_id', '=', $courseId)
                        ->where('module_id','=',$request->moduleId)->delete();
                
                //also delete the module items and content
                
                $moduleItems = ModuleItem::where('course_id', '=', $courseId)
                        ->where('module_id','=',$request->moduleId);
                foreach($moduleItems as $item)
                {
                    $this->deleteModuleItemsContent($courseId, $request->moduleId, $item->module_item_id);
                    $mItemkey = "{$courseId}-module-{$request->moduleId}-moduleItem-{$request->moduleItemId}";
                
                    //we can't just delete the module straight from cache cause we need to delete it from the module (in cache), etc
                    $cacheHelper->deleteModuleItemFromCache($mItemkey, $this->cacheTime);
                }
                ModuleItem::where('course_id', '=', $courseId)
                        ->where('module_id','=',$request->moduleId)->delete();
                
            }
              
            return $response;
        }
        catch(\GuzzleHttp\Exception\ClientException $e)//without the backslash the Exception won't be caught!
        {
            if ($e->hasResponse()) 
            {
                if ($e->getResponse()->getStatusCode() ==="404")
                { //This can be caused because the module/moduleItem doesn't exist. Just return (skip the part where we try to delete item from cache)
                    return null;
                }
            }
            return "An error occurred. Unable to delete module data";
        }

    }
    
    public function postModuleData(ModulesRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        $scope = "module";
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        
        if($request->moduleId)
        {// "we're creating a moduleItem";
            $urlPieces[] = "modules/{$request->moduleId}/items";

            $modItem = $request->getModuleItem();
            foreach($modItem as $key => $value) {
                if ($value)
                {
                    $urlArgs[] = "module_item[{$key}]={$value}";
                }
            }
        }
        else
        {//we're creating a module obj
        
            $urlPieces[] = "modules";

            $modItem = $request->getModule();
            foreach($modItem as $key => $value) {
                if(($value) && ($key ==="prerequisite_module_ids") && is_array($value))
                {
                    foreach($value as $prereq)
                    {
                        $urlArgs[] = "module[prerequisite_module_ids][]={$prereq}";
                    }
                }
                else if ($value)
                {
                    $urlArgs[] = "module[{$key}]={$value}";
                }
            }
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        
        $response = GuzzleHelper::makeRequest($request, $url);
        
        //update cache if request was successful
        if ($response->getStatusCode() ==="200")
        {
            $newlyUpdated= \GuzzleHttp\json_decode($response->getBody());
            if(isset($newlyUpdated->module_id))
            {
                //it's a module item
                $this->updateModuleItemInCache($newlyUpdated);
            }
            else 
            {
                //it's a module
                $this->updateModuleInCache($newlyUpdated);
            }
            return 1;
        }
        else
        {
            return 0;
        }
    }
     
    
    /*
     * SUBMISSIONS
     */
    public function processSubmissionsRequest(SubmissionsRequest $request)
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
        
        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        $response = GuzzleHelper::makeRequest($request, $url);
        return $response->getBody();
        
    }
    
    /*
     * ASSIGNMENTS
     */
    public function processAssignmentsRequest(AssignmentsRequest $request)
    {//api/v1/courses/:course_id/assignments
        
        echo "getting from Canvas";
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $singleRow = false;
            
        $urlPieces[] = "assignments";
        
        //we'll get ALL the assignments in cache. Then we'll filter it out from cache;
//        if($request->getAssignment_id())
//        {
//            $singleRow = true;
//            $urlPieces[] = $request->getAssignment_id();
//        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";
        $urlArgs[]="per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        
        $response = GuzzleHelper::makeRequest($request, $url);

        return $this->processCanvasAssignmentData(json_decode($response->getBody()), $courseId, $singleRow);
        
    }
    
    public function processAssignmentGroupsRequest(AssignmentGroupsRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        $singleRow = false;
        $urlPieces[] = "assignment_groups";
        
        $urlArgs[]="include[]=assignments";
        //Attach token
        $urlArgs[]="access_token={$token}";
        $urlArgs[]="per_page=5000";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
        
        $response = GuzzleHelper::makeRequest($request, $url);
        
        return $this->processCanvasAssignmentGroupsData(json_decode($response->getBody()), $courseId, $singleRow);
        
    }
    /*
     * private functions
     */
    /*
     * MODULES
     */
    //These cache-updating functions are here because of the particular way Canvas handles updates. After we update something in the API, canvas 
    //returns the item that was just updated so we can just use it to update our Cache if the request was successful. This is specific enough
    //to canvas that we can't put it in the CacheHelper class cause other LMS's implementation will be different
    private function updateModuleInCache($moduleArr)
    {   
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        $mod = $this->processSingleModule($moduleArr, $courseId);//by calling this function we are automatically updating this item in cache
    }
    
    private function updateModuleItemInCache($moduleItemArr)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        $this->processSingleModuleItem($courseId, $moduleItemArr);
    }
    
    
    private function deleteModuleItemsContent($courseId, $moduleId, $moduleItemId)
    {
        $contentArr = Content::where('module_item_id','=', $moduleItemId);
        foreach($contentArr as $item)
        {   
            $contentKey = "{$courseId}-module-{$moduleId}-moduleItem-{$moduleItemId}-content-{$item->content_id}";
            $cacheHelper = new CacheHelper();
            $cacheHelper->deleteObjFromCache($contentKey);
        }

        Content::where('module_item_id','=', $moduleItemId)->delete();
    }
    
    private function processCanvasModuleData($data, $courseId)
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
        $module = Module::firstOrNew(array('module_id' => $moduleRow->id));//('moduleId','=',$module->id);
        $module->module_id = $moduleRow->id;
        $module->course_id = $courseId;//do we need this?
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
            $moduleItems = $this->saveModuleItems($moduleRow->items, $courseId);
            $module->module_items = $moduleItems;
        }
        $module->save();
        //We need to assign the moduleItems AFTER we've converted the module to an array because the moduleItems are a laravel relationship
        //that is only loaded this way. If we don't set the module Items this way they won't be stored as a property of this module in Cache
        $moduleArr = $module->toArray();
        $moduleArr['module_items'] = $module->module_items->toArray();

        $key = "{$courseId}-module-{$module->module_id}";
        
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {//toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!
            
            Cache::forever($key, $moduleArr);
        }
        else
        {
            Cache::put($key, $moduleArr, $this->cacheTime);
        }

        
        return $module;
    }
    
    private function saveModuleItems($moduleItems, $courseId)
    {
        $allItems = array();
        
        foreach($moduleItems as $mItem){
            $moduleArr =$this->processSingleModuleItem($courseId, $mItem);
            array_push($allItems, $moduleArr);
        }
        
        return $allItems;
    }
    
    private function processSingleModuleItem($courseId, $mItem)
    {
        $moduleItem = ModuleItem::firstOrNew(array(
            'module_id' => $mItem->module_id,
            'module_item_id'=> $mItem->id
        ));
        $moduleItem->module_item_id = $mItem->id;
        $moduleItem->module_id = $mItem->module_id;
        $moduleItem->course_id = $courseId;
        $moduleItem->position = $mItem->position;
        $moduleItem->title = $mItem->title;
        $moduleItem->indent = $mItem->indent;
        $moduleItem->type = $mItem->type;

        if(isset($mItem->published)){$moduleItem->published = $mItem->published;}

        //if we don't have contentId we'll use the module_item_id. This is for tagging purposes
        $contentId = 0;
        if(isset($mItem->content_id))
        {
            $moduleItem->content_id = $mItem->content_id;
        }
        else
        {
            $moduleItem->content_id = $mItem->id;
        }

        if(isset($mItem->html_url)){$moduleItem->html_url = $mItem->html_url;}
        if(isset($mItem->url)){$moduleItem->url = $mItem->url;}
        if(isset($mItem->page_url)){$moduleItem->page_url = $mItem->page_url;}
        if(isset($mItem->external_url)){$moduleItem->external_url = $mItem->external_url;}
        if(isset($mItem->new_tab)){$moduleItem->new_tab = $mItem->new_tab;}
        if(isset($mItem->completion_requirement)){$moduleItem->completion_requirement = json_encode($mItem->completion_requirement);}
        if(isset($mItem->content_details) && isset($mItem->type))
        {
            $content = $this->saveContentDetails($courseId, $mItem->module_id, $mItem->id, $moduleItem->content_id, $mItem->type,$mItem->content_details);
            $moduleItem->content = $content;
        }

        $moduleItem->save();

        //We need to assign the moduleItems AFTER we've converted the module to an array because the moduleItems are a laravel relationship
        //that is only loaded this way. If we don't set the module Items this way they won't be stored as a property of this module in Cache
        $moduleArr = $moduleItem->toArray();
        $moduleArr['content'] = $moduleItem->content->toArray();

        $key = "{$courseId}-module-{$mItem->module_id}-moduleItem-{$mItem->id}";
//            $key="moduleItem-".$courseId.'-'.$moduleId.'-'.$mItem->id;

        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {
            Cache::forever($key, $moduleArr);
        }
        else
        {
            Cache::put($key, $moduleArr,$this->cacheTime);
        }
        
        return $moduleArr;
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
    
    
    /*
     * ASSIGNMENTS
     */
    private function processCanvasAssignmentData($data, $courseId, $singleRow)
    {
        $assignments= array();
        
        if($singleRow)
        {
            $assignments[] = $this->processSingleAssignment($data);
        }
        else
        {
            foreach($data as $row)
            {
                $assignments[] = $this->processSingleAssignment($row);
            }
            $key = "{$courseId}-assignments";

            if(Cache::has($key))
            {
                Cache::forget($key);
            }
            if($this->forever)
            {//toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!

                Cache::forever($key, $assignments);
            }
            else
            {
                Cache::put($key, $assignments, $this->cacheTime);
            }
        }
        
        return $assignments;
        
    }
    
    private function processSingleAssignment($row)
    {           
        $assignment = Assignment::firstOrNew(array('assignment_id' => $row->id));
        $assignment->assignment_id = $row->id;
        $assignment->assignment_group_id = $row->assignment_group_id;
        $assignment->name = $row->name;
        if(($assignment->description)) {$assignment->description=$row->description;}
        
        $format = "Y-m-d\TH:i:sO";
        if(isset($row->due_at))
        {
            $due_at= DateTime::createFromFormat($format, $row->due_at);
            $assignment->due_at = $due_at->format('c');
        }
        if(isset($row->lock_at))
        {
            $lock_at= DateTime::createFromFormat($format, $row->lock_at);
            $assignment->lock_at = $lock_at->format('c');
            
        }
        if(isset($row->unlock_at))
        {
            $unlock_at= DateTime::createFromFormat($format, $row->unlock_at);
            $assignment->unlock_at = $unlock_at->format('c');
        }
        if(isset($row->all_dates)){$assignment->all_dates = $row->all_dates;}
        if(isset($row->course_id)){$assignment->course_id = $row->course_id;}
        if(isset($row->html_url)){$assignment->html_url = $row->html_url;}
        if(isset($row->points_possible)){$assignment->points_possible = $row->points_possible;}
        if(isset($row->locked_for_user)){$assignment->locked_for_user = $row->locked_for_user;}
        if(isset($row->quiz_id)){$assignment->quiz_id = $row->quiz_id;}
        if(isset($row->additional_info)){$assignment->additional_info = $row->additional_info;}
        
        $assignment->save();
        
        $key = "{$row->course_id}-assignment_id-{$assignment->assignment_id}";
        
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {//toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!
            
            Cache::forever($key, $assignment->toArray());
        }
        else
        {
            Cache::put($key, $assignment->toArray(), $this->cacheTime);
        }

        
        return $assignment;
    }
    
    private function processCanvasAssignmentGroupsData($data, $courseId, $singleRow)
    {
        if(($singleRow) || count($data)===1)
        {
            return $this->processSingleAssignmentGroup($data, $courseId);
        }
        else
        {
            $assignmentGroupArray = array();
            foreach($data as $row)
            {
                $assignmentG = $this->processSingleAssignmentGroup($row, $courseId);
                $assignmentGroupArray[] = $assignmentG;
            }
            
            $key = "{$courseId}-assignment_groups";
        
            if(Cache::has($key))
            {
                Cache::forget($key);
            }
            if($this->forever)
            {//toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!

                Cache::forever($key, $assignmentGroupArray);
            }
            else
            {
                Cache::put($key, $assignmentGroupArray, $this->cacheTime);
            }
                return $assignmentGroupArray;
            }
    }
    
    private function processSingleAssignmentGroup($row, $courseId)
    {
        $assignmentGroup = AssignmentGroup::firstOrNew(array('assignment_group_id' => $row->id));
        $assignmentGroup->assignment_group_id = $row->id;
        $assignmentGroup->name = $row->name;
        $assignmentGroup->position = $row->position;
        if(isset($row->rules)){ $assignmentGroup->rules = json_encode($row->rules);}
        $assignmentGroup->group_weight = $row->group_weight;
        
        
        $assigArr = $assignmentGroup->toArray();//in order to save eloquent models to the DB we need to convert them to arrays because they are closures
        //by nature and we can't store them in cache like that. We'll convert them to arrays, then set their *many relationships
        //in the array (relationships aren't maintained when an eloquent model is converted to an array
        if(isset($row->assignments))
        {
            $arr = array();
            $assignments = $row->assignments;
            foreach($assignments as $row)
            {
                $assignment = $this->processSingleAssignment($row);
                $arr[] = $assignment;
            }
            $assignmentGroup->assignments = $arr;
            
            $assigArr["assignments"] = $arr;
        }
        
        $assignmentGroup->save();
        $key = "{$courseId}-assignment_group_id-{$assignmentGroup->assignment_group_id}";
        
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        if($this->forever)
        {   
            Cache::forever($key, $assigArr);
        }
        else
        {
            Cache::put($key, $assigArr, $this->cacheTime);
        }

        
        return $assigArr;
    }
}
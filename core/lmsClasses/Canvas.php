<?php namespace Delphinium\Core\lmsClasses;

use \DateTime;
use Delphinium\Core\DB\DbHelper;
use Delphinium\Core\Cache\CacheHelper;
use Delphinium\Core\Exceptions\InvalidParameterInRequestObjectException;
use Delphinium\Core\Guzzle\GuzzleHelper;
use Delphinium\Core\Models\CacheSetting;
use Delphinium\Core\Models\ModuleItem;
use Delphinium\Core\Models\Content;
use Delphinium\Core\Models\Module;
use Delphinium\Core\Models\OrderedModule;
use Delphinium\Core\Models\Assignment;
use Delphinium\Core\Models\AssignmentGroup;
use Delphinium\Core\Models\Submission;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\UpdatableObjects\Module as UpdatableModule;
use Delphinium\Core\UpdatableObjects\ModuleItem as UpdatableModuleItem;
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
        if($cacheSetting)
        {
            
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
        }//if no cacheSetting it's because we won't be storing that data type in cache (such as submissions)
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

        
        
        echo json_encode($response);
        return $this->processCanvasModuleData(json_decode($response->getBody()), $courseId);
    }
    
    public function putModuleData(ModulesRequest $request)
    {
        if(!$request->getModuleId())
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

        $urlPieces[] = "modules/{$request->getModuleId()}";
        
        if($request->getModuleItemId())
        {//updating a module item
            
            $urlPieces[] = "items/{$request->getModuleItemId()}";
            $scope = "module_item";
            $urlArgs = $this->buildModuleItemUpdateArgs($request->getModuleItem());
        }
        else
        {//updating a module
            $urlArgs = $this->buildModuleUpdateUrl($request->getModule());
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 
echo $url;
//return;
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
        if(!$request->getModuleId())
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

        $urlPieces[] = "modules/{$request->getModuleId()}";
        
        if($request->getModuleItemId())
        {
            $isModuleItem = true;
            $urlPieces[] = "items/{$request->getModuleItemId()}";
            $scope = "module_item";
        }
        
        //Attach token
        $urlArgs[]="access_token={$token}";

        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs); 

        try
        {
            //delete from Canvas
            $response = GuzzleHelper::makeRequest($request, $url);
            if($response->getStatusCode() ==="200")
            {
                //if the CANVAS delete was successful we need to 
                //delete the same data from cache & from the DB
                $cacheHelper = new CacheHelper();
                $dbHelper = new DbHelper();
                /*
                 * NOTE:
                 * Cascading delete is not yet supported in OctoberCMS, so we have to do all the cascading deletes manually. 
                 * See https://github.com/octobercms/october/issues/419
                 */
                if($isModuleItem)
                {//delete the module item and its contents from CACHE
                    $moduleItemKey = "{$courseId}-module-{$request->getModuleId()}-moduleItem-{$request->getModuleItemId()}";
                    $cacheHelper->deleteModuleItemFromCacheCascade($moduleItemKey, true, $this->cacheTime);
    //                $cacheHelper->deleteModuleFromCacheCascade($request->moduleId, $this->forever, $this->cacheTime);

                    //delete the module item and its contents from DB
                    $dbHelper->deleteModuleItemCascade($request->getModuleId(), $request->getModuleItemId());
                     //delete module item's contents
    //                $this->deleteModuleItemsContent($courseId, $request->moduleId, $request->moduleItemId);
                }
                else
                {//DELETE MODULE

                //this will delete this module, its module items, and the content from DB
                $dbHelper->deleteModuleCascade($courseId, $request->getModuleId());

                //this will delete this module, its module items, and the content from Cache
                $cacheHelper->deleteModuleFromCacheCascade($request->getModuleId(), $this->forever, $this->cacheTime);

                ////delete module, module items, and contents
    //                $moduleKey = "{$courseId}-module-{$request->moduleId}";
    //                $cacheHelper->deleteObjFromCache($moduleKey);
    //                
    //                Module::where('course_id', '=', $courseId)
    //                        ->where('module_id','=',$request->moduleId)->delete();
    //                //also delete the module items and content
    //                
    //                $moduleItems = ModuleItem::where('course_id', '=', $courseId)
    //                        ->where('module_id','=',$request->moduleId);
    //                foreach($moduleItems as $item)
    //                {
    //                    $this->deleteModuleItemsContent($courseId, $request->moduleId, $item->module_item_id);
    //                    $mItemkey = "{$courseId}-module-{$request->moduleId}-moduleItem-{$request->moduleItemId}";
    //                
    //                    //we can't just delete the module straight from cache cause we need to delete it from the module (in cache), etc
    //                    $cacheHelper->deleteModuleItemFromCache($mItemkey, $this->cacheTime);
    //                }
    //                ModuleItem::where('course_id', '=', $courseId)
    //                        ->where('module_id','=',$request->moduleId)->delete();
    //                
                }
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

        
        if($request->getModuleId())
        {// "we're creating a moduleItem";
            $urlPieces[] = "modules/{$request->getModuleId()}/items";

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
            $newlyCreated= \GuzzleHttp\json_decode($response->getBody());
            
            if(isset($newlyCreated->module_id))
            {
                //it's a module item
                $modItem = $this->updateModuleItemInCache($newlyCreated);
                
//                echo json_encode($modItem);
                if($request->getModuleItem()->getTags())
                {//add the tags!
                    echo "has tags";
                    $tags = $request->getModuleItem()->getTags();
                    
                    $dbHelper = new DbHelper();
                    $dbHelper->addTags($modItem['content_id'], $tags, $courseId);
                }
                else
                {
//                    echo "doesn't have tags";
                }
            }
            else 
            {
                //it's a module
                $this->updateModuleInCache($newlyCreated);
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
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $domain = $_SESSION['domain'];
        $token = $_SESSION['userToken'];
        $courseId = $_SESSION['courseID'];
        $userId = $_SESSION['userID'];
        
        $urlPieces= array();
        $urlArgs = array();
        $urlPieces[]= "https://{$domain}/api/v1/courses/{$courseId}";

        
        //MULTIPLE ASSIGNMENTS AND POTENTIALLY MULTIPLE USERS
        if($request->getMultipleAssignments())
        {//GET /api/v1/courses/:course_id/students/submissions
            $urlPieces[]="students/submissions";
            
            //STUDENT IDS
            //student_ids can be "All", or a list of actual studentIds
            if($request->getMultipleStudents() && $request->getAllStudents())
            {
                $urlArgs[]="student_ids[]=all";
            }
            else if($request->getMultipleStudents())
            {
                $ids = json_encode($request->getStudentIds());
                $urlArgs[]="student_ids[]={$ids}";
            }
            else
            {
                $urlArgs[]="student_ids[]={$userId}";
            }
            
            //ASSIGNMENT IDS
            //assignment_ids can be a list of assignmentIds, or if empty, all assignments will be returned
            
            $assignmentIds = $tags = implode(',', $request->getAssignmentIds());
            $urlArgs[]= "assignment_ids[]={$assignmentIds}";
                
        }
        //SINGLE ASSIGNMENT, MULTIPLE USERS
        else if($request->getMultipleStudents())
        {   
            // GET /api/v1/courses/:course_id/assignments/:assignment_id/submissions
            $urlPieces[]= "assignments";
            //grab the first assignment id. Shouldn't have more than one (all this has been validated in the SubmissionsRequest constructor)
            $urlPieces[]= $request->getAssignmentIds()[0];
            $urlPieces[]= "submissions";
            
        }
        //SINGLE ASSIGNMENT, SINGLE USER
        else
        {//GET /api/v1/courses/:course_id/assignments/:assignment_id/submissions
            if(($request->getAssignmentIds()))
            {            
                $urlPieces[]= "assignments"; //input1
                $urlPieces[]= $request->getAssignmentIds()[0]; // get the first assignment id from the array (there shouldn't be more than one anyway)
                $urlPieces[] = "submissions";
                $urlPieces[] = $userId;
            }

        }
        
        //Attach token
        $urlArgs[]="access_token={$token}&per_page=5000";
        
        $url = GuzzleHelper::constructUrl($urlPieces, $urlArgs);
        
        echo $url;return;
        $response = GuzzleHelper::makeRequest($request, $url);
        
        return $this->processCanvasSubmissionData(json_decode($response->getBody()));
        
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
    
    private function buildModuleUpdateArgs(UpdatableModule $module)
    {
        $urlArgs = array();
        foreach($module as $key=>$value)
        {
            if($key === "published")
            {
                $converted_res = ($value) ? 'true' : 'false';
                $urlArgs[] = "module[{$key}]={$converted_res}";
                continue;
            }
            if($key === "prerequisite_module_ids")
            {
                $urlArgs[] = "module[prerequisite_module_ids][]={$value}";
                continue;
            }
            $urlArgs[] = "module[{$key}]={$value}";
        }
        return $urlArgs;
    }
    private function buildModuleItemUpdateArgs(UpdatableModuleItem $moduleItem)
    { 
        $urlArgs = array();
        
        foreach($moduleItem as $key=>$value)
        {//cannot update content_id or page_url (as per Canvas API)
            if(($key === "content_id")||($key === "page_url")||$key==="tags"||$key==="type")
            {
                continue;
            }
            if($key === "published")
            {
                $converted_res = ($value) ? 'true' : 'false';
                $urlArgs[] = "module_item[{$key}]={$converted_res}";
                continue;
            }
            if($key === "completion_requirement_type")
            {
                $urlArgs[] = "module_item[completion_requirement][type]={$value}";
                continue;
            }
            if($key === "completion_requirement_min_score")
            {
                $urlArgs[] = "module_item[completion_requirement][min_score]={$value}";
                continue;
            }
            if($value)
            {
                $urlArgs[] = "module_item[{$key}]={$value}";
            }
        }
        return $urlArgs;
    }
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
        return $mod;
    }
    
    private function updateModuleItemInCache($moduleItemArr)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        return $this->processSingleModuleItem($courseId, $moduleItemArr);
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
//        echo " -- processing from CANVAS -- ";
        
        
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
        
        //since we are updating our DB and CACHE with fresh Canvas data we MUST check against our DB and make sure we don't have "old" modules stored
        $dbHelper = new DbHelper();
        $dbHelper->qualityAssuranceModules($courseId, $moduleIdsArray);
                
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
        $orderedMod = $this->retrieveOrderedModuleInfo($moduleRow->id, $courseId);
        
        if($orderedMod)
        {
            $module->order = $orderedMod->order;
            $module->parent_id = $orderedMod->parent_id;
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
    
    private function retrieveOrderedModuleInfo($moduleId, $courseId)
    {
        return OrderedModule::where('course_id', '=', $courseId)
                                    ->where('module_id','=',$moduleId)->first();
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
        if(isset($mItem->type))
        {
            $contentDetails;
            if(isset($mItem->content_details))
            {
                $contentDetails = $mItem->content_details;
            }
            else
            {
                $contentDetails = null;
            }
            $content = $this->saveContentDetails($courseId, $mItem->module_id, $mItem->id, $moduleItem->content_id, $mItem->type,$contentDetails);
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
        
        if(isset($row->due_at))
        {
            $due_at= DateTime::createFromFormat(DateTime::ISO8601, $row->due_at);
            $assignment->due_at = $due_at->format('c');
        }
        if(isset($row->lock_at))
        {
            $lock_at= DateTime::createFromFormat(DateTime::ISO8601, $row->lock_at);
            $assignment->lock_at = $lock_at->format('c');

        }
        if(isset($row->unlock_at))
        {
            $unlock_at= DateTime::createFromFormat(DateTime::ISO8601, $row->unlock_at);
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
    
    /*
     * SUBMISSIONS
     */
    private function processCanvasSubmissionData($data)
    {
        $submissions = array();
        if(gettype($data)==="array")//we have a single submission
        { //we have multiple submissions
            foreach($data as $row)
            {
                $subm = $this->processSingleSubmission($row);
                $submissions[] = $subm;
            }
        }
        else
        {  
            $submissions[] = $this->processSingleSubmission($data);
        }
        return $submissions;
    }
    
    private function processSingleSubmission($row)
    {
        $submission = new Submission();
        $submission->submission_id = $row->id;
        $submission->assignment_id = $row->assignment_id;
        if(isset($row->course)){$submission->course = $row->course;}
        if(isset($row->attempt)){$submission->attempt = $row->attempt;}
        if(isset($row->body)){$submission->body = $row->body;}
        if(isset($row->grade)){$submission->grade = $row->grade;}
        if(isset($row->grade_matches_current_submission)){$submission->grade_matches_current_submission = $row->grade_matches_current_submission;}
        if(isset($row->html_url)){$submission->html_url = $row->html_url;}
        if(isset($row->preview_url)){$submission->preview_url = $row->preview_url;}
        if(isset($row->score)){$submission->score = $row->score;}
        if(isset($row->submission_comments)){$submission->submission_comments = $row->submission_comments;}
        if(isset($row->submission_type)){$submission->submission_type = $row->submission_type;}
        if(isset($row->submitted_at)){$submission->submitted_at = $row->submitted_at;}
        if(isset($row->url)){$submission->url = $row->url;}
        if(isset($row->user_id)){$submission->user_id = $row->user_id;}
        if(isset($row->grader_id)){$submission->grader_id = $row->grader_id;}
        if(isset($row->late)){$submission->late = $row->late;}
        if(isset($row->assignment_visible)){$submission->assignment_visible = $row->assignment_visible;}
        
        return $submission;
    }
}
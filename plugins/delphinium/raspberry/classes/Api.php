<?php namespace Delphinium\Raspberry\Classes;

use Illuminate\Support\Facades\Cache;
use Delphinium\Raspberry\Models\Module;
use Delphinium\Raspberry\Models\OrderedModule;
use Delphinium\Raspberry\Models\ModuleItem;
use Delphinium\Raspberry\Models\Content;
use Delphinium\Raspberry\Models\Tag;
use Delphinium\Raspberry\Classes\ApiHelper;

/*
 * This class interacts with Canvas API. 
 */
class Api{
    public $cacheTime;

   
    /*
    * Retrieves all the modules for the given course Id
    * @url: the URL to the API (with parameters)
    * @courseId: The id of the course for which the modules will be returned
    * @cachedData: If true, returns cached data. If false, returns fresh data
    */
   public function getApiModules($url, $courseId, $cachedData, $cacheTime, $forever)
   {//TODO: we can get the courseId from the URL
           $this->cacheTime = $cacheTime;
           $items = array();
           $moduleIds = $this->getModuleIds($courseId);
           //if we need cachedData and we have the keys
           if($cachedData && (count($moduleIds) > 0))
           {
                   foreach($moduleIds as $id)
                   {
                           $key = 'module'.$courseId."-".$id;
                           if (Cache::has($key))
                           {
                                   $value = Cache::get($key);
                                   array_push($items,$value);
                           }
                           else
                           {
                               //if we don't have the key, return all items from the API
                               $mods = $this->getModulesFromApiNoCache($url, $courseId, $cacheTime, $forever);
                               return $mods;
                           }
                   }
                   return $items;
           }
           else
           {
               //if we don't want cached data or we didn't have the keys
               $modules = $this->getModulesFromApiNoCache($url, $courseId, $cacheTime, $forever);
               return $modules;
           }

   }
	
    public function getModulesFromApiNoCache($url, $courseId,$cacheTime, $forever)
    {
    	
	$moduleIdsArray = array();
        $apiHelper = new ApiHelper();
    	$data = $apiHelper->get_api_data($url);
		
            foreach($data as $moduleRow)
            {
                //we'll create an array with all the moduleIds that belong to this courseId
                $moduleIdsArray[] = $moduleRow->id;

                //check if module exists
                $module = Module::firstOrNew(array('moduleId' => $moduleRow->id));//('moduleId','=',$module->id);
                $module->moduleId = $moduleRow->id;
                $module->courseId = $courseId;//do we need this?
                $module->name = $moduleRow->name;
                $module->position = $moduleRow->position;
                $module->unlock_at = $moduleRow->unlock_at;
                $module->require_sequential_progress = $moduleRow->require_sequential_progress;
                $module->publish_final_grade = $moduleRow->publish_final_grade;
                $csv = $this->makeCSVofArray($moduleRow->prerequisite_module_ids);
                
                
                $module->prerequisite_module_ids = $csv;
                
                
                $module->published = $moduleRow->published;
                $module->items_count = $moduleRow->items_count;
                
                
                if(isset($moduleRow->items)){
                    //save moduleItems
                    $moduleItems = $this->saveModuleItems($moduleRow->items, $courseId, $moduleRow->id , $cacheTime, $forever);
                    $module->moduleItems = $moduleItems;
                }
                $module->save();
                $items[] = $module;

                //toArray is the key here! an Eloquent model is a closure and won't be serialized unless we first convert it to an Array!!!
                $key = 'module'.$courseId."-".$module->moduleId;
                $forever?Cache::forever($key, $module->toArray()):Cache::put($key, $module->toArray(), $cacheTime);
            }
			
            //put in Cache a list of all the module keys for this course
            $moduleIdsKey = "moduleIds-courseId".$courseId;
            $forever?Cache::forever($moduleIdsKey, $moduleIdsArray):Cache::put($moduleIdsKey, $moduleIdsArray, $cacheTime);

            //return an array of module models
            return $items;
    }
    
    private function makeCSVofArray($array)
    {
        $csv = implode(",",$array);
        return $csv;
    }
    
    private function saveModuleItems($moduleItems, $courseId, $moduleId, $cacheTime, $forever)
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
            
            if(isset($mItem->content_id)){$module->content_id = $mItem->content_id;}
            if(isset($mItem->html_url)){$module->html_url = $mItem->html_url;}
            if(isset($mItem->url)){$module->url = $mItem->url;}
            if(isset($mItem->page_url)){$module->page_url = $mItem->page_url;}
            if(isset($mItem->external_url)){$module->external_url = $mItem->external_url;}
            if(isset($mItem->new_tab)){$module->new_tab = $mItem->new_tab;}
            if(isset($mItem->completion_requirement)){$module->completion_requirement = json_encode($mItem->completion_requirement);}
            
            if(isset($mItem->content_details) && (isset($mItem->content_id)) && isset($mItem->type))
            {
                    $content = $this->saveContentDetails($courseId, $moduleId, $mItem->id, $mItem->content_id, $mItem->type,$mItem->content_details, $cacheTime, $forever);
                    $module->content = $content;
                    $module->save();
            }
            
            
//            $module->save();
            array_push($allItems, $module);
            $key="moduleItem-".$courseId.'-'.$moduleId.'-'.$mItem->id;
            
            if(Cache::has($key))
            {
                Cache::forget($key);
            }
            $forever?Cache::forever($key, $module->toArray()):Cache::put($key, $module->toArray(),$cacheTime);
        }
        
        //add the module Items to the module object
//        $moduleObj = Module::where('moduleId','=',$moduleId)->first();
//        $moduleObj->moduleItems()->saveMany($allItems);
        return $allItems;

    }
    
    public function getModuleItems($courseId, $moduleId)
    {
        //add the module Items to the module object
        $moduleObj = Module::where('moduleId','=',$moduleId)->first();
        if(isset($moduleObj->moduleItems)){
            return $moduleObj->moduleItems;
        }
        else
        {
            $items = $this->getApiModuleItems($courseId,$moduleId);
            return $this->saveModuleItems($items, $courseId, $moduleId, false);
        }
    }
    
    private function saveContentDetails($courseId, $moduleId, $itemId, $contentId, $type, $contentDetails, $cacheTime, $forever)
    {
        $key="content-".$courseId.'-'.$moduleId.'-'.$itemId.'-'.$contentId;
        
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
        
        $content = Content::firstOrNew(array('content_id'=>$contentId));//('content_id','=',$contentId);

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
        
        $forever?Cache::forever($key, $content->toArray()):Cache::put($key, $content->toArray(), $cacheTime);
        return $content;
    }
    
    public function tagContent($content_id, $tags)
    {
        $content = Content::where('$content_id','=', $content_id)->first();
        $content->tags = $tags;
        $content->save();
    }
	
    
    public function getModules($url, $courseId, $cachedData, $cacheTime, $forever)
    {
    	$fullModules = array();
    	
    	$apiModules = $this->getApiModules($url, $courseId, $cachedData, $cacheTime, $forever);
        
    	foreach($apiModules as $module)
    	{
          
            $orderedMod = OrderedModule::where('courseId', '=', $courseId)
                                    ->where('moduleId','=',$module['moduleId'])->first();

            if(!is_null($orderedMod))
            {
                $moduleFromDB = Module::where('courseId', '=', $courseId)
                                    ->where('moduleId','=',$module['moduleId'])->first();
                
                
                $moduleFromDB->parentId = $orderedMod->parentId;
                $moduleFromDB->order = $orderedMod->order;
                $moduleFromDB->save();
            }

    	}
        
        $allModules = Module::where('courseId', '=', $courseId)->orderBy('parentId')->get();
        
    	foreach($allModules as $module)
        {
//            $fullModules[$module->moduleId] = $module->toArray();
            //add each module's content
            $mItems = ModuleItem::where('module_id', '=', $module->moduleId)->get();
            $mItemArray= array();
            
            foreach($mItems as $moduleItem)
            {
                //get each item's tags
                $tags = $this->getTags($moduleItem->content_id);
                $tempArr = $moduleItem->toArray();
                $tempArr["content_details"] = $moduleItem->content->toArray();
//                $hg=$moduleItem->content;
                (sizeof($tags)>0 ? $tempArr["tags"] = $tags: $tempArr["tags"] = "");
                
                array_push($mItemArray,$tempArr);
            }
            
            
            
            $modArray = $module->toArray();
            $modArray["items"] = $mItemArray;
            array_push($fullModules,$modArray);
            $key = 'module'.$courseId."-".$module->moduleId;
            $forever?Cache::forever($key, $modArray):Cache::put($key, $modArray, $cacheTime);
        }
    	return $fullModules;
    }
    
    //This function saves the additional data added by the Iris Manager
    public function saveIrisModules($courseId, $modules, $cacheTime)
    {
        $ordered = array();


        foreach($modules as $item)
        {
            $modFromDb = OrderedModule::where('courseId', '=', $item->courseId)->where('moduleId', '=', $item->moduleId)->first();
            if($modFromDb)
            {
                $module = $modFromDb;
            }
            else
            {
                $module = new OrderedModule();//OrderedModule::firstOrNew(array("moduleId" => $modId));

            }
//            

            $module->moduleId = $item->moduleId;
            $module->parentId = $item->parentId;
            $module->courseId = $item->courseId;
            $module->order = $item->order;
            $module->save();
            
            $key = 'module'.$courseId."-".$module->moduleId;
            
            if(Cache::has($key))
            {
                $modFromCache = Cache::get($key);
                Cache::forget($key);//forget item, update and reinsert
                $modFromCache['parentId'] = $item->parentId;
                
                Cache::put($key, $modFromCache, $cacheTime);
            }
        }
		
        //TODO: we cache the ordering of the modules?
        //Cache::put('orderedModules-'.$courseId, $ordered, 10);
    }
    
    /*
     * Updates a module with the given data
     * @moduleId= the Id of the module to be updated
     * @keyValueParams = a key/value array with the new parameters to be updated
     */
    public function updateModule($moduleId, $keyValueParams,$cacheTime)
    {//TODO: make this a parameter/read it from session, or something
    	$courseId = 343331;
        
        $paramString ='';
        foreach($keyValueParams as $key => $value)
        {	//module[name]=Test
            //TODO: fix this string to attach an ampersand at the end of each line, if there are more than one parameters being updated
            $paramString .= 'module['.$key.']'.'='.($value);
            
             
        }
        $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules/'.$moduleId.'?access_token=14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY&'.$paramString;       
        
        $data = APIHelper::post_api_data($url);

        //UPDATE CACHE WITH THE NEW VALUE
        
        $module = Module::where('moduleId','=',intval($moduleId))->first();
        $module->$key = $value;
        $module->save();
        
        $key = 'module'.$courseId."-".$moduleId;
        Cache::forget($key);
        Cache::put($key, $module->toArray(), $cacheTime);
		
        return $data;
    }
    
    public function getApiModuleItems($courseId,$moduleId)
    {
        $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules/'.$moduleId.'/items?access_token=14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY'; 
        $apiHelper = new ApiHelper();
    	$data = $apiHelper->get_api_data($url);
        
        return $data;
    } 
    
    public function getTags($contentId)
    {
        $content = Content::where('content_id', '=', $contentId)->first();
        
        if(isset($content->tags))
        {
            return $content->tags;
        }
        else
        {
            return null;
        }
    }
    
    public function getAvailableTags($courseId)
    {
        $tags = Tag::where('course_id', '=', $courseId)->first();
        
        if($tags)
        {
            return $tags->tags;
        }
        else
        {
            return $tags;
        }
    }
    
    public function updateAvailableTags($courseId, $newTags)
    {
        $tags = Tag::firstOrNew(array('course_id' => $courseId));
        
        $possibleTags =  $tags->tags;
        if(strlen($possibleTags)>0)
        {
            $currentTagsArr = explode(', ', $possibleTags);
            $c = array_merge($currentTagsArr,$newTags);
            $unique = array_unique($c);
            $tagString =implode(', ', $unique);
        }
        else 
        {
            $tagString =implode(', ', $newTags);
        }
        
        $tags->course_id = $courseId;
        $tags->tags = $tagString;
        $tags->save();
    }
    
    public function addTags($contentId, $newTags, $courseId)
    {
//        var_dump($newTags);
        $content = Content::where('content_id', '=', $contentId)->first();
        if(strlen($content->tags)>0){
//            var_dump("stre");
            $current = explode(', ', $content->tags);
       
            $c = array_merge($current,$newTags);
            $unique = array_unique($c);
//            var_dump("unique-".$unique);
            //convert array to string
            $tagString =implode(', ', $unique);
        }
        else 
        {
            $tagString =implode(', ', $newTags);
        }
        
        
            $content->tags =$tagString;
            $content->save();
            
            $this->updateAvailableTags($courseId, $newTags);
            return $content->tags;
        
    }
    
    public function deleteTag($contentId, $tag)
    {
        $content = Content::where('content_id', '=', $contentId)->first();
        
        $currTagStr = $content->tags;
        
        $current = explode(', ', $currTagStr);
        
        $new = array();
        $new[] = $tag;
        $filtered = array_diff($current, $new);
        
        
        $tagString =implode(', ', $filtered);
        
        $content->tags = $tagString;
        $content->save();
        return $content->tags;
    }
    
    public function getCourse($courseId)
    {
        $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'&access_token=14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY';
        $apiHelper = new ApiHelper();
    	$data = $apiHelper->get_api_data($url);
        return $data;
    }
    
    public function getModuleStates($courseId, $studentId)
    {
        $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules/?student_id='.$studentId.'&access_token=14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY&per_page=5000';       
        $moduleIdsArray = array();
        $apiHelper = new ApiHelper();
        $data = $apiHelper->get_api_data($url);
        
        if(isset($data->errors))
        {
            return "Unauthorized user";    
        }
        else
        {
            $moduleStateInfo = array();

                foreach($data as $moduleRow)
                {
                    //we'll create an array with all the moduleIds that belong to this courseId
                    $mod = new \stdClass();
                    $mod->moduleId = $moduleRow->id;
                    $mod->state = $moduleRow->state;
                    if(isset($moduleRow->completed_at)){$mod->completed_at = $moduleRow->completed_at;}
                    array_push($moduleStateInfo, $mod);
                }
            return $moduleStateInfo;
        }

    }
    
    public function getStudentSubmissions($courseId, $studentId)
    {
        //GET /api/v1/courses/:course_id/students/submissions
//        ?student_ids[]=items&include[]=content_details
        $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/students/submissions?student_ids[]='.$studentId.'&access_token=14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY&per_page=5000';       
        $moduleIdsArray = array();
        $apiHelper = new ApiHelper();
        
        $data = $apiHelper->get_api_data($url);
//            var_dump($data);
        if(isset($data->errors))
        {
            return "An error occurred";    
        }
        else
        {
            return $data;
//            return;
//            $moduleStateInfo = array();
//
//                foreach($data as $moduleRow)
//                {
//                    //we'll create an array with all the moduleIds that belong to this courseId
//                    $mod = new \stdClass();
//                    $mod->id = $moduleRow->id;
//                    $mod->state = $moduleRow->state;
//                    if(isset($moduleRow->completed_at)){$mod->completed_at = $moduleRow->completed_at;}
//                    array_push($moduleStateInfo, $mod);
//                }
//            return $moduleStateInfo;
        }

    
    }
    
    private function getModuleIds($courseId)
    {
            $array = array();
            $key = "moduleIds-courseId".$courseId;
            if(Cache::has($key))
            {
                    $array = Cache::get($key);
            }
            return $array;
    }
    
    protected function makeModuleFromApi($moduleItem)
    {			
        $module = new Module();
        $module->moduleId = $moduleItem->id;
        $module->courseId = $courseId;//do we need this?
        $module->name = $moduleItem->name;
        $module->position = $moduleItem->position;
        $module->unlock_at = $moduleItem->unlock_at;
        $module->require_sequential_progress = $moduleItem->require_sequential_progress;
      	$module->publish_final_grade = $moduleItem->publish_final_grade;
//   	$module->prerequisite_module_ids = $moduleItem->prerequisite_module_ids;//array
        $module->published = $moduleItem->published;
        $module->items_count = $moduleItem->items_count;
// 		$module->items = $moduleItem->items;//aray
				
        $module->save();
        return $module;		
		
    }
    
    
}

<?php namespace Delphinium\Core\lmsClasses;

use Delphinium\Raspberry\Models\Module;
use Delphinium\Raspberry\Models\ModuleItem;
use Illuminate\Support\Facades\Cache;

class Canvas
{
    
    public $forever = false;
    public $cacheTime = 5;
    
    function __construct($forever, $cacheTime) 
    {
        $this->forever = $forever;
        $this->cacheTime = $cacheTime;
    }
    
    public function processModuleData($data, $courseId)
    {
        $items = array();
        $moduleIdsArray = array();
        
        //need to see whether we have an array of modules or a single module
        if(is_array($data))
        {
            foreach($data as $moduleRow)
            {
                 //we'll create an array with all the moduleIds that belong to this courseId
                $moduleIdsArray[] = $moduleRow->id;
                $module = $this->processSingleModule($moduleRow, $courseId);
                $items[] = $module;
            }
        }
        else if(is_object($data))
        {
            $module = $this->processSingleModule($data, $courseId);   
            $items[] = $module;
        }
                
        

        //put in Cache a list of all the module keys for this course
        $moduleIdsKey = "moduleIds-courseId".$courseId;
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
       echo json_encode($moduleRow);
       return;
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

        $key = 'module'.$courseId."-".$module->moduleId;
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
            $key="moduleItem-".$courseId.'-'.$moduleId.'-'.$mItem->id;
            
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
        $key="content-".$courseId.'-'.$moduleId.'-'.$itemId.'-'.$contentId;
        
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
}
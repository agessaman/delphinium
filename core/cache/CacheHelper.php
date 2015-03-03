<?php namespace Delphinium\Core\Cache;

use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Raspberry\Models\Module;
use Delphinium\Raspberry\Models\ModuleItem;
use Illuminate\Support\Facades\Cache;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CacheHelper
{
    public function searchModuleDataInCache(ModulesRequest $request)
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
            
            if(Cache::has($key))
            {
                $data = Cache::get($key);
                return $data;
            }
            else
            {
                return null;
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
                        $value = Cache::get($key);
                        array_push($items,$value);
                    }
                    else
                    {
                        return null;
                    }
                }
                return $items;
            }
            else
            {
                return null;
            }
            
            
        }
    }

    public function updateCache(ModulesRequest $request)
    {         
        $key="";
        if($request->moduleId)
        {
            if($request->moduleItemId)
            {
                $modItem = ModuleItem::where('module_item_id','=',$request->moduleItemId);
                $moduleArr = $moduleItem->toArray();
                $moduleArr['content'] = $moduleItem->content->toArray();
                
                $key = "{$courseId}-module-{$moduleId}-moduleItem-{$mItem->id}";
            }
            else
            {
                $module = Module::where('moduleId','=',$request->moduleId);
                $moduleArr = $module->toArray();
                $moduleArr['moduleItems'] = $module->moduleItems->toArray();
        
                $key = "{$courseId}-module-{$module->moduleId}";
                
            }
            
            if(Cache::has($key))
            {
                $data = Cache::get($key);
                $data;
            }
        }
        
    }
    public function searchAssignmentDataInCache(AssignmentsRequest $request)
    {
        
    }

}

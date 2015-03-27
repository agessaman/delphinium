<?php namespace Delphinium\Core\Cache;

use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
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

    public function searchAssignmentDataInCache(AssignmentsRequest $request)
    {
        $courseId = $_SESSION['courseID'];
        $key = "";
        if($request->getAssignment_id())
        {//they want a specific assignment
            $key = "{$courseId}-assignment_id-{$request->getAssignment_id()}";
            if(Cache::has($key))
            {
                return Cache::get($key);
            }
            else
            {
                return null;
            }
        }
        else
        {//return all assignments
            $key = "{$courseId}-assignments";
            if(Cache::has($key))
            {
                return Cache::get($key);
            }
            else
            {
                return null;
            }
            
            
        }
    }
    
    public function serchAssignmentGroupDataInCache(AssignmentGroupsRequest $request)
    {
        $courseId = $_SESSION['courseID'];
        $singleGroup = false;
        if($request->getAssignment_group_id())
        {
            $key = "{$courseId}-assignment_group_id-{$request->getAssignment_group_id()}";
            $singleGroup = true;
        }
        else
        {
            $key = "{$courseId}-assignment_groups";
        }
        
        if(Cache::has($key))
        {
            if(!$request->getInclude_assignments())
            {
                $groups = Cache::get($key);
                
                if(!$singleGroup)
                {
                    $res = array();
                    foreach($groups as $group)
                    {
                        if(isset($group['assignments']))
                        {
                            $group['assignments'] = array();
                        }
                        $res[] = $group;
                    }
                    return $res;
                }
                else
                {
                    $groups['assignments'] = array();
                    return $groups;
                }
            }
            else
            {
                return Cache::get($key);
            }
            
        }
        else
        {
            return null;
        }
    }

    public function deleteObjFromCache($key)
    {
        if(Cache::has($key))
        {
            Cache::forget($key);
        }
    }
    
    public function deleteModuleFromCacheCascade($moduleId, $forever, $cacheTime)
    {
        $courseId = $_SESSION['courseID'];
        $moduleKey = "{$courseId}-module-{$moduleId}";
        if(Cache::has($moduleKey))
        {
            $module = Cache::get($moduleKey);
            
            foreach($module['module_items'] as $moduleItem)
            {
                $moduleItemKey = "{$courseId}-module-{$moduleItem['module_id']}-moduleItem-{$moduleItem['module_item_id']}";
                $this->deleteModuleItemFromCacheCascade($moduleItemKey, true, $cacheTime);
            }
            
            Cache::forget($moduleKey);
        }
        
        
        
//        clear this module from the list of modules in cache
        $moduleIdsKey = "{$courseId}-moduleIds";
        $allModules = Cache::get($moduleIdsKey);
        
        $arr = array_diff($allModules, array($moduleId));
        if($forever)
        {
            Cache::forever($moduleIdsKey, $arr);
        }
        else
        {
            Cache::put($moduleIdsKey, $arr, $cacheTime);
        }
        
    }
    
    public function deleteModuleItemFromCacheCascade($moduleItemKey, $deleteFromModuleList, $cacheTime)
    {
        $courseId = $_SESSION['courseID'];
        if(Cache::has($moduleItemKey))
        {
            $moduleItem = Cache::get($moduleItemKey);
            $moduleId = $moduleItem['module_id'];
            foreach($moduleItem['content'] as $contentItem)
            {
                //get each moduleItem's content and delete from cache
                $contentKey = "{$courseId}-module-{$moduleId}-moduleItem-{$moduleItem['module_item_id']}-content-{$contentItem['content_id']}";
                $this->deleteContentFromCache($contentKey);
            }
            
            //delete the module Item
            Cache::forget($moduleItemKey);
        }
        
        if($deleteFromModuleList)
        {//loop through this module item's module and delete this moduleItem from the module's list of module items
            
            $moduleKey = "{$courseId}-module-{$moduleId}";
            if(Cache::has($moduleKey))
            {
                $module = Cache::get($moduleKey);
                $mdItems = $module['module_items'];
                foreach ($mdItems as $key=>$value)
                {
                    if ($value["module_item_id"]===$moduleItem['module_item_id']) {
                       unset($mdItems[$key]);
                       $mdItems = array_values($mdItems);
                       break;
                    }
                }
                
                //update the module's items
                $module['items_count'] = count($mdItems);
                $module["module_items"] = $mdItems;
                Cache::forget($moduleKey);
                
                if($cacheTime<1)
                {
                    Cache::forever($moduleKey, $module);
                }
                else
                {
                    Cache::put($moduleKey, $module, $cacheTime);
                }
            }
        }
    }
    
    public function deleteContentFromCache($contentKey)
    {
        $this->deleteObjFromCache($contentKey);
    }
}

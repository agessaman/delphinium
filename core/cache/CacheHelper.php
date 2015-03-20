<?php namespace Delphinium\Core\Cache;

use Delphinium\Core\RequestObjects\AssignmentsRequest;
use \Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
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
//                echo "want module item";
                $key = "{$courseId}-module-{$request->moduleId}-moduleItem-{$request->moduleItemId}";
            }
            else
            {
                $key = "{$courseId}-module-{$request->moduleId}";
            }
            if(Cache::has($key))
            {
                echo " found key in cache ";
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
//            echo "want all mods";
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
                echo "found in cache";
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
                echo "found in cache";
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
        
        if($request->getAssignment_group_id())
        {
            $key = "{$courseId}-assignment_group_id-{$request->getAssignment_group_id()}";
        }
        else
        {
            $key = "{$courseId}-assignment_groups";
        }
        
        if(Cache::has($key))
        {
            echo "has key";
            if(!$request->getInclude_assignments())
            {
                $groups = Cache::get($key);
//                foreach($groups as $group)
//                {
//                    echo json_encode($group)."--";
//                    $group['assignments'] = array();
////                    $results[] = $group;
//                }
////                return $results;
                return $groups;
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
    
    public function deleteModuleItemFromCache($moduleItemKey, $cacheTime)
    {
        if(Cache::has($moduleItemKey))
        {
            $moduleItem = Cache::get($moduleItemKey);
            $modItemId = $moduleItem["module_item_id"];
            $moduleId = $moduleItem["module_id"];
            $courseId = $moduleItem["course_id"];
            //delete module item from cache
            Cache::forget($moduleItemKey);
            
            //also delete it from its module's array of module items
            $moduleKey = "{$courseId}-module-{$moduleId}";
            if(Cache::has($moduleKey))
            {
                $module = Cache::get($moduleKey);
                $mdItems = $module['module_items'];
    
                foreach ($mdItems as $key=>$value)
                {
                    if ($value["module_item_id"]===$modItemId) {
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
    
}

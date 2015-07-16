<?php namespace Delphinium\Roots\DB;

use Delphinium\Roots\Models\ModuleItem;
use Delphinium\Roots\Models\Module;
use Delphinium\Roots\Models\Content;
use Delphinium\Roots\Models\Tag;
use Delphinium\Roots\Models\OrderedModule;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\AssignmentGroup;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;

class DbHelper
{
    /*
     * GET
     */
    public function getOrderedModuleByModuleId($courseId, $moduleId)
    {
        $orderedModule = OrderedModule::where('module_id', '=', $moduleId)->where('course_id', '=',$courseId)->first();
        return $orderedModule;
    }
    
    public function getTagsByContentId($content_id)
    {
        $content = Content::where('content_id', '=', $content_id)->first();
        return $content->tags;
    }
    
    public function getModuleData(ModulesRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        if($request->getModuleId())
        {   
            if($request->getModuleItemId())
            {
                return ModuleItem::with('content')->where(array(
                    'module_id' => $request->getModuleId(),
                    'module_item_id'=> $request->getModuleItemId()
                ))->first();
            }
            else
            {
                return Module::with('module_items.content')->where(array(
                    'module_id' => $request->getModuleId(),
                    'course_id' => $courseId
                ))->first();
            }
        }
        else
        {//if no moduleId was found they must want all the modules
            $modules = Module::with('module_items.content')->where(array(
                'course_id' => $courseId
            ))->get();
            
            return $modules;
        }
    }
    
    public function getAssignmentData(AssignmentsRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        if($request->getAssignment_id())
        {//they want a specific assignment
            return Assignment::where(array(
                    'assignment_id' => $request->getAssignment_id(),
                    'course_id' => $courseId
                ))->first();
        }
        else
        {//return all assignments
            return Assignment::where(array(
                    'course_id' => $courseId
                ))->get();
        }
    }
    
    public function getAssignmentGroupData(AssignmentGroupsRequest $request)
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        if($request->getAssignment_group_id())
        {
            if($request->getInclude_assignments())
            {
                return AssignmentGroup::with('assignments')->where(array(
                    'assignment_group_id' => $request->getAssignment_group_id() 
               ))->first();
            }
            else
            {
                return AssignmentGroup::where(array(
                    'assignment_group_id' => $request->getAssignment_group_id() 
               ))->first();
            }
            
        }
        else
        {
            if($request->getInclude_assignments())
            {
                return AssignmentGroup::with('assignments')->where(array(
                     'course_id' => $courseId
                ))->get();
            }
            else
            {
                return AssignmentGroup::where(array(
                     'course_id' => $courseId
                ))->get();
            }
        }
    }
    
    public function getAvailableTags()
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        $tags = Tag::where(array('course_id' => $courseId))->first();
        
        if((!is_null($tags))&&($tags->tags))
        {
            $possibleTags =  $tags->tags;
            if(strlen($possibleTags)>0)
            {
                $tagArr = explode(", ",$tags->tags);
                if(in_array("Optional", $tagArr)||in_array("optional", $tagArr)){
                }//doing !in_array gives false positives
                else
                {
                    array_push($tagArr,"Optional");
                }
                if (in_array("Description", $tagArr)||in_array("description", $tagArr)){
                }
                else
                {
                    array_push($tagArr,"Description");
                }
                return implode(", ",$tagArr);
            }
            else
            {
                return "Optional, Description";
            } 
        }
        else
        {
            return "Optional, Description";
        }
        
    }
    /*
     * UPDATE
     */
    public function addTags($contentId, $newTagsStr, $courseId)
    {
        $content = Content::where('content_id', '=', $contentId)->first();
            
            if(!is_null($content))//this could be due to the moduleItem not having an Id
            {
                $newTags = explode(', ', $newTagsStr);
                if(strlen($content->tags)>0){
                    $current = explode(', ', $content->tags);

                    $c = array_merge($current,$newTags);
                    $unique = array_unique($c);
                    //convert array to string
                    $tagString =implode(', ', $unique);
                }
                else 
                {
                    $tagString =$newTagsStr;
                }


                $content->tags =$tagString;
                $content->save();

                $this->updateAvailableTags($courseId, $newTags);
                return $content->tags;
            }
            else
            {
                return null;
            }
    }
    
    public function updateTags($contentId, $newTagsStr, $courseId)
    {
        $content = Content::where('content_id', '=', $contentId)->first();
            
        if(!is_null($content))//
        {
            $content->tags =$newTagsStr;
            $content->save();

            $newTags = explode(', ', $newTagsStr);
            $this->updateAvailableTags($courseId, $newTags);
            return $content->tags;
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
    
    public function updateOrderedModule($module)
    {
        $orderedModule = OrderedModule::firstOrNew(
                array(
                    'course_id' => $module->course_id, 
                    'module_id' => $module->module_id
                )
        );
        $orderedModule->module_id = $module->module_id;
        $orderedModule->parent_id = $module->parent_id;
        $orderedModule->course_id = $module->course_id;
        $orderedModule->order = $module->order;
        $orderedModule->save();
        
        $moduleDB = Module::where(array(
            'module_id' => $module->module_id, 
            'course_id' => $module->course_id
        ))->first();
        
        $moduleDB->parent_id = $module->parent_id;
        $moduleDB->order = $module->order;
        $moduleDB->save();
        
        return $orderedModule;
    }
    
    
    /*
     * DELETE
     */
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
    
    //These cascading delete methods exist because OctoberCMS doesn't support cascading delete yet. 
    //Inn https://github.com/octobercms/october/issues/419 it says that the bug has been fixed and the code commited, 
    //but I just downloaded the RC version of OctoberCMS and it doesn't include that fix (at least the cascading delete isn't
    //happening). -DZ 3/26/2015
    public function deleteAllModuleItemsByModuleIdCascade($moduleId)
    {
        $moduleItems = ModuleItem::where('module_id','=',$moduleId)->get();
        //delete all content
        foreach($moduleItems as $item)
        {
            $this->deleteAllContentByModuleItem($item->module_item_id);
        }
        
        ModuleItem::where('module_id','=',$moduleId)->delete();
    }
    
    public function deleteAllContentByModuleItem($moduleItemId)
    {
        Content::where('module_item_id','=', $moduleItemId)->delete();
    }
    
    public function deleteModuleCascade($courseId, $moduleId)
    {
        $this->deleteAllModuleItemsByModuleIdCascade($moduleId);
        
        Module::where('course_id', '=', $courseId)
                ->where('module_id','=',$moduleId)->delete();
    }
    
    public function deleteModuleItemCascade($moduleId, $moduleItemId)
    {
        //delete the this module item's content
        $this->deleteAllContentByModuleItem($moduleItemId);
        
        //delete the actual ModuleItem
        ModuleItem::where('module_item_id', '=', $moduleItemId)
                        ->where('module_id','=',$moduleId)->delete();
    }
    
    public function qualityAssuranceModules($courseId, $currenModuleIdsArr)
    {
        $modules = Module::where('course_id','=',$courseId)->select('module_id')->get();
        $fromDBArr = array();
        foreach($modules as $mod)
        {
            $fromDBArr[] = $mod['module_id'];
        }
        
        $toBeDeleted =array_diff($fromDBArr,$currenModuleIdsArr);
        
        foreach($toBeDeleted as $module)
        {//TODO: verify cascading delete
            Module::where('course_id','=',$courseId)->where('module_id','=',  intval($module))->delete();
        }
    }
    
    public function qualityAssuranceModuleItems($courseId, $moduleItemIds)
    {
        $modulesItems = ModuleItem::where('course_id','=',$courseId)->select('module_item_id')->get();
        $fromDBArr = array();
        foreach($modulesItems as $item)
        {
            $fromDBArr[] = $item['module_item_id'];
        }
        
        $toBeDeleted =array_diff($fromDBArr,$moduleItemIds);
        
        foreach($toBeDeleted as $module_item_id)
        {
            ModuleItem::where('course_id','=',$courseId)->where('module_item_id','=',  intval($module_item_id))->delete();
        }
    }
}

<?php namespace Delphinium\Core\DB;

use Delphinium\Core\Models\ModuleItem;
use Delphinium\Core\Models\Module;
use Delphinium\Core\Models\Content;
use Delphinium\Core\Models\Tag;
use Delphinium\Core\Models\OrderedModule;

class DbHelper
{
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
        {
            Module::where('course_id','=',$courseId)->where('module_id','=',  intval($module))->delete();
        }
    }
    
    public function getTagsByContentId($content_id)
    {
        $content = Content::where('content_id', '=', $content_id)->first();
        return $content->tags;
    }
    
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
                echo "content was null";
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
        return $orderedModule;
    }
}

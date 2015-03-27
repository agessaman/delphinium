<?php namespace Delphinium\Core\DB;

use Delphinium\Core\Models\ModuleItem;
use Delphinium\Core\Models\Module;
use Delphinium\Core\Models\Content;

class DbHelper
{
    //These cascading delete methods exist because OctoberCMS doesn't support cascading delete yet. 
    //Inn https://github.com/octobercms/october/issues/419 it says that the bug has been fixed and the code commited, 
    //but I just downloaded the RC version of OctoberCMS and it doesn't include that fix. -DZ 3/26/2015
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
    
}

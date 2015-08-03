<?php namespace Delphinium\Stem\Classes;

use Delphinium\Roots\Models\OrderedModule;

class ManagerHelper 
{
    public function recursive($courseId, array $array, &$flatArray, $parentId = 1, $counter=false, $order = 0)
    {//the main-level elements will have no parent, so we will assign them a parentId of 1.
                
        foreach($array as $level)
        {
            $mod = new OrderedModule();
            
            $mod->module_id = $level->module_id;
            $mod->parent_id = $parentId;
            $mod->course_id = $courseId;
            $mod->order = $order;

            array_push($flatArray, $mod);

            if(isset($level->children)&&(sizeof($level->children)>0))
            {//order will be zero based
                $innerOrder = 0;
                $parentId = $level->module_id;
                $counter = true;
                $this->recursive($courseId, $level->children, $flatArray, $parentId, $counter, $innerOrder);
                $parentId = $level->parent_id;
                $counter = false;
            }

            //to manage the position of the children elements
            $order++;
        }
        return $flatArray;
    }
     
    public function buildTree(&$elements, $parentId = 1) {
        $branch = array();
        $order = 0;
        $newItems= array();
        foreach ($elements as $module) {
            if ($module['parent_id'] == $parentId) 
            {
                $children = $this->buildTree($elements, $module['module_id']);
                if ($children) {
                    $module['children'] = $children;
                }
                else
                {
                    $module['children'] = array();
                }
                $branch[] = $module;
                unset($elements[$module['module_id']]);
            }
        }
        return $branch;

    }
    
    public function makeItemParent($arrWithOldParent, $newParent)
    {
        $newParent['children'] = array();
        $allItems = $arrWithOldParent["children"];
        $arrWithOldParent["children"] = [];
        
        array_unshift($allItems,$arrWithOldParent);//insert the old parent at the top of the array
        
        $firstParentId=$newParent['module_id']; 
        
        foreach($allItems as $value)
        {
            $value['parent_id'] = $firstParentId;
            array_push($newParent['children'], $value);//append all children
        }

        $newParent['parent_id'] = 1;
        $newParent['order'] = 0;
        
        $this->fixChildrenOrder($newParent['children']);
        $result[] = $newParent;
        return $result;
    }
    
    private function fixChildrenOrder($multiDArray)
    {
        $order = 0;
        foreach($multiDArray as $module)
        {
            $module['order'] = $order;
            if($module['children'])
            {
                $this->fixChildrenOrder($module['children']);
            }
            $order++;
        }
    }
    
    
}
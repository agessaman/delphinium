<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Stem\Classes;

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
                $altparentId = $level->module_id;
                $counter = true;
                $this->recursive($courseId, $level->children, $flatArray, $altparentId, $counter, $innerOrder);
                $counter = false;
            }
            //to manage the position of the children elements
            $order++;
        }
        return $flatArray;
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
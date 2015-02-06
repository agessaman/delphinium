<?php namespace Delphinium\Iris\Classes;

use Delphinium\Raspberry\Classes\Api;
use Delphinium\Raspberry\Models\OrderedModule;
use Exception;


/*
 * This class will retrieve the necessary information from Raspberry to build out the iris chart.
 */
class Iris 
{

	public function getModules($courseId, $token, $cacheTime, $forever, $studentId=NULL)
	{
		//TODO: make this domain and courseId configurable
            
		$url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules?include[]=items&include[]=content_details&access_token='.$token.'&per_page=5000';
                
                if(!is_null($studentId))
                {
                    $url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules/?student_id='.$studentId.'&include[]=items&include[]=content_details&access_token='.$token.'&per_page=5000';
                }
		$api = new Api();
		
		//return an array of Module objects
                $cacheData = true;
                if($cacheTime<1) 
                {
                    $cacheData = false;
                    $cacheTime = 10;
                }
//                var_dump($cacheTime);
		$data = $api->getModules($url, $courseId, $cacheData, $cacheTime, $forever);
		
		
		return $data;
	}
        
        public function saveIrisModules($array, $courseId, $cacheTime)
        {
            $api = new Api();
            $api->saveIrisModules($courseId, $array, $cacheTime);
        }
        
        public function getCourse($courseId)
        {
            $api = new Api();
            $return = $api->getCourse($courseId);
            return $return;
        }
        
        public function getAvailableTags($courseId)
        {
            $api = new Api();
            $return = $api->getAvailableTags($courseId);
            return $return;
        }
        
    public function recursive($courseId, array $array, &$flatArray, $parentId = 1, $counter=false, $order = 0)
    {//the main-level elements will have no parent, so we will assign them a parentId of 1.
    //            
        foreach($array as $level)
        {
            $mod = new OrderedModule();
            
            $mod->moduleId = $level->moduleId;
            $mod->parentId = $parentId;
            $mod->courseId = $courseId;
            $mod->order = $order;

            array_push($flatArray, $mod);

            if(isset($level->children)&&(sizeof($level->children)>0))
            {
                //order will be zero based
                $innerOrder = 0;
                $parentId = $level->moduleId;
                $counter = true;
                $this->recursive($courseId, $level->children, $flatArray, $parentId, $counter, $innerOrder);
                $parentId = $level->parentId;
                $counter = false;
            }

            //to manage the position of the children elements
            $order++;
//                    var_dump($order);
        }
        return $flatArray;
    }
       
    public function buildTree(array &$elements, $parentId = 1) {
        $branch = array();
        $order = 0;
        $newItems= array();
        foreach ($elements as $module) {
            //if there are any new Modules that we got by refreshing Cache they will have a parentId=0. We need to add them to the array.
//            if($module['parentId'] == 0)
//            {
//                var_dump($module['name']);
//                array_push($newItems, $module);
//                unset($elements[$module['moduleId']]);
//            }
            if ($module['parentId'] == $parentId) 
            {
//                var_dump($module['parentId']);
                $children = $this->buildTree($elements, $module['moduleId']);
                if ($children) {
                    $module['children'] = $children;
                }
                else
                {
                    $module['children'] = array();
                }
                $branch[] = $module;
//                var_dump(count($elements));
                unset($elements[$module['moduleId']]);
//                var_dump("\n");
//                var_dump(count($elements));
            }
        }

//        if(count($branch)>1)//if we do have parent/child relationships, append the new modules to the end
//        {
////            var_dump(count($branch));
//            foreach($newItems as $module)
//            {
//                var_dump($branch[0]["name"]);
//                $module["parentId"] = $branch[0]["moduleId"];
//                unset($newItems[$module['moduleId']]);
//            }
//        }
        return $branch;

    }
    public function makeItemParent($arrWithOldParent, $newParent)
    {
        $allItems = $arrWithOldParent["children"];
        $arrWithOldParent["children"] = [];
        
//        $newOrder = count($newParent['children']);
//        $arrWithOldParent['order'] = $newOrder;//insert this item after the new item's children
        array_unshift($allItems,$arrWithOldParent);//insert the old parent at the top of the array
        
        $firstParentId=$newParent['moduleId']; 
        
        foreach($allItems as $value)
        {
            $value['parentId'] = $firstParentId;
            array_push($newParent['children'], $value);//append all children
        }

//        $this->fixOrder($allItems);
        $newParent['parentId'] = 1;
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
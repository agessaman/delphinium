<?php namespace Delphinium\Iris\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Iris\Classes\Iris as IrisClass;
/**
 * Description of Stem
 *
 * @author Damaris Zarco
 */
class Stem  extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Stem Manager',
            'description' => 'Stem: Module Manager'
        ];
    }
    
    public function onRun()
    {   
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/jquery.min.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular.min.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/angular-ui-tree.js");
//        $this->addJs("/plugins/delphinium/iris/assets/javascript/tree.js");
//    	$this->addCss('/plugins/delphinium/iris/assets/css/module-tree.css');	
//        $this->addCss('/plugins/delphinium/iris/assets/css/angular-ui-tree.min.css');
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $this->page['courseId'] = $_SESSION['courseID'];
        
        //get ALL module data
        $this->prepareData(false);
    }
    
    public function onRefreshCache()
    {
       $this->prepareData(true);
    }
    
    private function prepareData($freshData)
    {
//        \Cache::flush();
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $moduleData = $roots->modules($req);
        
//        echo json_encode($moduleData);
        $iris = new IrisClass();
        $result = $iris->newBuildTree($moduleData);
        
//        echo "   --------------------------------------------------------------------------------------------------   ". PHP_EOL;
//        echo json_encode($result);
//        return;
        
        
        
        $tempArray =array();

        if(count($result)<1) //there weren't any parent-child relationships
        {
            $parent;
            $allChildren;
            $final = array();

            //The parent will be the first PUBLISHED item
            $firstItem;
            foreach($moduleData as $item)
            {
                if($item['published'] == "1")
                {
                    $firstItem = $item;
                    break;
                }
            }
            $newArr = $this->unsetValue($moduleData, $firstItem);//remove parent from array
            $firstParentId=$firstItem["module_id"];
            $i=0;
            foreach($newArr as $item)
            {
                $item["parent_id"] = $firstParentId;
                //each item must have a parentId of the first module
                $item["children"] = [];
                $item["order"] = $i;
                $final[] = $item;
                $i++;
            }

            //remove the first Item (which is the parent)
            $firstItem["parent_id"] = 1;
            $firstItem["children"]=$final;
            $firstItem["order"]=0;

            $tempArray[] = $firstItem;
            
        }
        else
        {
            $tempArray = $result;
        }

        $this->page['moduleData'] = json_encode($tempArray);
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        }
       
        $courseId = $_SESSION['courseID'];
        $tags = $iris->getAvailableTags($courseId);
        if(strlen($tags)>0)
        {
            $tags = explode(', ', $tags);
        }
        else
        {
            $tags = [];
        }
        $this->page['avTags'] = json_encode($tags);
        
    }
    
    private function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }
}
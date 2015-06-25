<?php namespace Delphinium\Iris\Components;

use Delphinium\Stem\Models\Home as IrisCharts;
use Delphinium\Roots\Roots;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Cms\Classes\ComponentBase;

class Iris extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Iris Chart',
            'description' => 'This chart displays all the modules of a course and the student\'s progress in it'
        ];
    }
    
    public function onRender()
    {
        
        $this->addJs("/plugins/delphinium/iris/assets/javascript/d3.v3.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/jquery.min.js");
        $this->addJs("/plugins/delphinium/iris/assets/javascript/newSunburst.js");
        $this->addCss("/themes/demo/assets/vendor/font-awesome/css/font-awesome.css");
        $this->addCss("/themes/demo/assets/vendor/font-autumn/css/font-autumn.css");
        $this->addCss("/plugins/delphinium/iris/assets/css/main.css");
        
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        
        $courseId = $_SESSION['courseID'];
        $this->page['courseId'] = $courseId;
        $this->page['userId'] = $_SESSION['userID'];
        
        
        //Filter by parent node if it has been configured
        $defaultNode = 1;
        $filter = $this->property('filter',$defaultNode);
        $this->page['filter'] = $filter;
        $finalData;
        
        $freshData = false;
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
        $this->page['rawData'] = json_encode($moduleData);
        $modArr = $moduleData->toArray();
        
        if($filter===$defaultNode)
        {///get all items     
            $finalData = $this->buildTree($modArr,1);
        }
        else
        {//filter by node
            $filterObj = array_filter(
                $modArr,
                function ($e) use ($filter) {
                    return $e['module_id'] === $filter;
                }
            );
            $obj = array_shift($filterObj);
            $finalData = $this->buildTree($modArr,$obj['parent_id'], $filter);
        }
        
    	$this->page['graphData'] = json_encode($finalData);
    }
    public function defineProperties()
    {
        return [
            'filter' => [
                'title'   => 'Filter',
                'description' => 'Display only this module and its children in Iris',
                'placeholder' => 'Select a parent node',
                'type'    => 'dropdown'
            ]
        ];
    }

    public function getFilterOptions()
    {
        $req = new ModulesRequest(ActionType::GET, null, null, true, 
                    true, null, null , false);
            $roots = new Roots();
            $moduleData = $roots->modules($req);
            $names=array();
            foreach($moduleData as $item)
            {
                $names[$item->module_id] = $item->name;
            }
            return ($names);
    }   
   
    private function buildTree(array &$elements, $parentId = 1, $moduleFilter=null) {
        $branch = array();
        foreach ($elements as $key=>$module) {
            if($module['published'] == "1")//if not published don't include it
            {   
                if(!is_null($moduleFilter)&&($module['module_id']!=$moduleFilter))
                {//if we have a filter and this module doesn't match the filter, skip the item
                    unset($elements[$module['module_id']]);
                    continue;
                }
                if ($module['parent_id'] == $parentId) {
                    $children = $this->buildTree($elements, $module['module_id']);
                    if ($children) {
                        $module['children'] = $children;
                    }
                    $branch[] = $module;
                    unset($elements[$module['module_id']]);
                }
            }
        }

        return $branch;
    }
    
    
    
}
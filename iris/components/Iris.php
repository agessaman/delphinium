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
    
    public function onRun()
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
        $finalData = $this->prepareData($courseId, $moduleData);
    	$this->page['graphData'] = json_encode($finalData);
    }
    
    public function defineProperties()
    {
    	return [
            'getFilter' => [
            	'title'             => 'Filter Params',
             	'description'       => 'Choose the filter parameter for this chart',
             	'type'              => 'dropdown',
                'placeholder'       => 'Choose a tag',
        	]
    	];
    }
    
    public function getFilterOptions()
    {
    	$roots = new Roots();
        $arr = explode(',',$roots->getAvailableTags());
        return $arr;
    }
    
    private function buildTree(array &$elements, $parentId = 1) {
        $branch = array();
        foreach ($elements as $key=>$module) {
            if($module['published'] == "1")//if not published don't include it
            {   
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
    
    private function prepareData($courseId, $moduleData)
    {
        $modArr = $moduleData->toArray();
        $result = $this->buildTree($modArr,1);
        
        return $result;
    }
    
    
}
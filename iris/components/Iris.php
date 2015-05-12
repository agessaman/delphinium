<?php namespace Delphinium\Iris\Components;

use Delphinium\Iris\Models\Home as IrisCharts;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
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
            'cacheTime' => [
                'title'              => 'Cache Time',
                'description'        => 'For how long should we cache Iris\' data (mins)?',
                'type'              => 'dropdown',
                'placeholder'       => 'Select how long we should cache data for',
                'default'            => 20,
                'options'           => ['5'=>'5 mins', '10'=>'10 mins', '15'=>'15 mins',
                    '20'=>'20 mins','30'=>'30 mins','1440'=>'1 day',
                    '10080'=>'1 week','10081'=>'Forever',]
            ]
            
        ];
    }
    
    public function getChartNameOptions()
    {
        $slides = IrisCharts::all();
        $array_dropdown = ['0'=>'- select a chart - '];

        foreach ($slides as $slide)
        {
            $array_dropdown[$slide->id] = $slide->Name;
        }

        return $array_dropdown;
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
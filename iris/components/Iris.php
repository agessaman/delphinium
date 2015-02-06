<?php namespace Delphinium\Iris\Components;


use \DB;
use Validator;
use October\Rain\Support\ValidationException;
use Delphinium\Iris\Models\Home as IrisCharts;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Blackberry\Models\Developers as LtiConfigurations;
use Cms\Classes\ComponentBase;

class Iris extends ComponentBase
{
// 	public $chartName;
	
	public function componentDetails()
    {
        return [
            'name'        => 'Iris Chart',
            'description' => 'This chart displays all the modules of a course and the student\'s progress in it'
        ];
    }
    
    public function onRun()
    {	
        
//        \Cache::flush();
        $this->addJs('/plugins/delphinium/iris/assets/javascript/d3.v3.min.js');
        $this->addJs('/plugins/delphinium/iris/assets/javascript/jquery.min.js');
        $this->addJs('/plugins/delphinium/iris/assets/javascript/newSunburst.js');
//        $this->addCss('/themes/demo/assets/vendor/font-awesome/css/font-awesome.css');
        $this->addCss('/themes/demo/assets/vendor/font-autumn/css/font-autumn.css');
        $this->addCss('/plugins/delphinium/iris/assets/css/main.css');	
            	
    	//this component MUST be used in conjunction with the LTI component, so a session will already have been started
//    	session_start();
        
        $courseId = $_SESSION['courseID'];
        $userId=$_SESSION['userID'];
        $this->page['userId'] = $userId;
        $this->page['courseId'] = $courseId;
//        
        $encryptedToken = $_SESSION['userToken'];
        $decrypted =$encryptedToken;//\Crypt::decrypt($encryptedToken);
        
//        $courseId = 343331;
//        $this->page['userId'] = 1489289;
//        $this->page['courseId'] = $courseId;
//        $decrypted ="sdf";//\Crypt::decrypt($encryptedToken);
        
        
    	$iris = new IrisClass();
        
        $cacheTime = $this->property('cacheTime');
        $forever;
        
        if($cacheTime>10080)
        {
            $forever = true;
        }
        else
        {
            $forever = false;
        }
        
        $moduleData = $iris->getModules($courseId, $decrypted, $cacheTime, $forever);
        
        var_dump($moduleData);
        return;
        $this->page['rawData'] = json_encode($moduleData);
        $finalData = $this->prepareData($courseId, $moduleData);
//        var_dump($finalData);
        //need to add one parent to encompass all the modules
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
                if ($module['parentId'] == $parentId) {
                    $children = $this->buildTree($elements, $module['moduleId']);
                    if ($children) {
                        $module['children'] = $children;
                    }
    //                $branch[$module['moduleId']] = $module;
                    $branch[] = $module;
                    unset($elements[$module['moduleId']]);
                }
            }
        }

        return $branch;

    }
    
    private function prepareData($courseId, $moduleData)
    {
    	$iris = new IrisClass();
        $course = $iris->getCourse($courseId);
        $result = $this->buildTree($moduleData,1);
//        
    	$this->page['courseData'] = json_encode($course);
        return $result;
    }
    
    
}

?>
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
            'name'        => 'Iris Sunburst Chart',
            'description' => 'Sunburst chart representing the modules of a course and the student\'s progress in it'
        ];
    }
    
    public function onRun()
    {	
    
        $this->addJs('/plugins/delphinium/iris/assets/javascript/d3.v3.min.js');
        $this->addJs('/plugins/delphinium/iris/assets/javascript/jquery.min.js');
        $this->addJs('/plugins/delphinium/iris/assets/javascript/sunburst.js');
        //$this->addJs('/plugins/delphinium/iris/assets/javascript/newSunburst.js');
        $this->addCss('/themes/demo/assets/vendor/font-awesome/css/font-awesome.css');
        $this->addCss('/plugins/delphinium/iris/assets/css/main.css');	
    	
    	
    	session_start();
        $courseId = $_SESSION['courseID'];
	$userId=$_SESSION['userID'];
        $this->page['courseId'] = $courseId;
        $encryptedToken = $_SESSION['userToken'];
        
        $decrypted =$encryptedToken;//\Crypt::decrypt($encryptedToken);
    	$iris = new IrisClass();
    	$moduleData = $iris->getModules($courseId, $decrypted, 10, false);
        
        /*
        $output = array();
        $jsonEncoded;
        foreach($moduleData as $item)
        {
            $jsonEncoded = json_encode($item);
            array_push($output, $jsonEncoded);
        }
        */
        //$json = $moduleData->jsonSerialize();
    	$this->page['moduleData'] = json_encode($moduleData);
    	
    	
    }
    
   public function defineProperties()
    {
        return [
            'chartName' => [
                'title'        => 'Charts',
                'description'  => 'Choose the chart that will be displayed.',
                'type'         => 'dropdown',
                'default'      => '',
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
    
    
    public function getLtiInstanceOptions()
    {
    	$instances = LtiConfigurations::all();

        $array_dropdown = ['0'=>'- select an LTI configuration - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }
    
    
}

?>
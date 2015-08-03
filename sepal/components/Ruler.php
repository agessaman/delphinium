<?php namespace Delphinium\Sepal\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Grade;

class Ruler extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Ruler Component',
            'description' => 'Interface for managing rule groups'
        ];
    }

    public function onRun()
    {   
        $this->addJs("/plugins/delphinium/sepal/assets/javascript/angular.min.js");
        $this->addJs('/plugins/delphinium/sepal/assets/javascript/ui-bootstrap-tpls-0.12.1.min.js');
        $this->addJs("/plugins/delphinium/sepal/assets/javascript/ruler.js");
        $this->addCss('/plugins/delphinium/sepal/assets/css/bootstrap.min.css');
        
        $this->page['componentInstances'] = Grade::all();
    }
    
    public function defineProperties()
    {
        return [];
    }
    
    private function getComponentInstances()
    {
        return Grade::all();
    }

}
<?php namespace Delphinium\Xylum\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Xylum\Models\ComponentInstance;

class Manager extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Manager Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun() {
        $this->addJs("/plugins/delphinium/xylum/assets/javascript/angular.min.js");
        $this->addJs("/plugins/delphinium/xylum/assets/javascript/manager.js");
        $this->addCss('/plugins/delphinium/stem/assets/css/bootstrap.min.css');
        $this->prepareData();
    }
    
    private function prepareData()
    {
        $this->page['allComponents'] = ComponentInstance::all();
    }

}
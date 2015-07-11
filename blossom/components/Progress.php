<?php namespace Delphinium\Blossom\Components;

use Delphinium\Blade\Classes\Data\DataSource;
use Cms\Classes\ComponentBase;

class Progress extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Progress',
            'description' => 'Shows students progress toward finishing the course'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/progress.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/progress.css");

        $source = new DataSource();
        $res = $source->getAssignments(\Input::all());

        //var_dump($res);
        
        /*foreach ($res as &$value) {
            var_dump($value['assignment_id']);
        }*/

    }

}
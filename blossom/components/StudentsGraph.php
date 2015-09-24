<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class StudentsGraph extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'StudentsGraph',
            'description' => 'Shows graph of students progress'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/studentsgraph.css");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/studentsgraph.js");
    }

}
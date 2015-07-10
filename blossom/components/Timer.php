<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;

class Timer extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Timer',
            'description' => 'Counts down till end of the course'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/timer.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/timer.css");

        $this->roots = new Roots();
        $res = $this->roots->getCourse();
        
        $start = $res->start_at;
        $end = $res->end_at;
        $this->page['start'] = $start;
        $this->page['end'] = $end;
    
    }

    
        

}
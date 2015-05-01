<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class Competencies extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Competencies',
            'description' => 'Shows students completion of core Competencies'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
    }
}
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
        return [
            'Competencies' => [
                'title'        => 'Number of Competencies',
                'description'  => 'Enter number of Competencies',
                'type'         => 'string',
                'default'      => '3',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The number of Competencies is required and should be integer.'
            ],
            
            
            'Animate' => [
                'title'        => 'Animate',
                'type'         => 'dropdown',
                'default'      => 'true',
                'options'      => ['true'=>'True', 'false'=>'False']
            ],

            'Size' => [
                'title'        => 'Size',
                'type'         => 'dropdown',
                'default'      => 'Medium',
                'options'      => ['Small'=>'Small', 'Medium'=>'Medium', 'Large'=>'Large']
            ]
            
        ];
    }

    public function onRender()
    {
        $this->page['competencies'] = $this->property('Competencies');
        $this->page['competenciesAnimate'] = $this->property('Animate');
        $this->page['competenciesSize'] = $this->property('Size');
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
    }
}
<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class Experience extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Experience',
            'description' => 'Displays students experience'
        ];
    }

    public function defineProperties()
    {
        return [

            'XP' => [
                'title'        => 'Experience Points',
                'description'  => 'Enter Experience Points',
                'type'         => 'string',
                'default'      => '5800',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Experience points value is required and should be integer.'
            ],

            'experienceGrade' => [
                'title'        => 'Current Grade',
                'description'  => 'Enter Current Grade',
                'type'         => 'string',
                'default'      => '5968',
            ],

            'experienceBonus' => [
                'title'        => 'Bonus',
                'description'  => 'Enter Bonus',
                'type'         => 'string',
                'default'      => '200',
            ],

            'experiencePenalties' => [
                'title'        => 'Penalties',
                'description'  => 'Enter Penalties',
                'type'         => 'string',
                'default'      => '-32',
            ],

            'milestones' => [
                'title'        => 'Number of Milestones',
                'description'  => 'Enter Number of Milestones',
                'type'         => 'string',
                'default'      => '10',
                'validationPattern' => '^[0-9]+$',
            ],

            'maxXP' => [
                'title'        => 'Maximum Experience points',
                'description'  => 'Enter Maximum Experience points',
                'type'         => 'string',
                'default'      => '10500',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Maximum Experience points value is required and should be integer.'
            ],

            'startDate' => [
                'title'        => 'Course start date',
                'description'  => 'Enter course start date',
                'type'         => 'string',
                'default'      => '01/01/2015',
            ],

            'endDate' => [
                'title'        => 'Course end date',
                'description'  => 'Enter course end date',
                'type'         => 'string',
                'default'      => '12/31/2015',
            ],

            'date' => [
                'title'        => 'Current date',
                'description'  => 'Enter current date',
                'type'         => 'string',
                'default'      => '02/19/2015',
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
        $this->page['experienceAnimate'] = $this->property('Animate');
        $this->page['experienceXP'] = $this->property('XP');
        $this->page['experienceBonus'] = $this->property('experienceBonus');
        $this->page['experiencePenalties'] = $this->property('experiencePenalties');
        $this->page['maxXP'] = $this->property('maxXP');
        $this->page['milestones'] = $this->property('milestones');
        $this->page['startDate'] = $this->property('startDate');
        $this->page['endDate'] = $this->property('endDate');
        $this->page['date'] = $this->property('date');
        $this->page['experienceGrade'] = $this->property('experienceGrade');
        $this->page['experienceSize'] = $this->property('Size');
    }
    
    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/experience.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/experience.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/font-awesome.min.css");
    }

}
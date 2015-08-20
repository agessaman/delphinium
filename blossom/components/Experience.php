<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Experience as ExperienceModel;

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

            'Instance' => [
                'title' => 'Instance',
                'description' => 'Select the Experience instance',
                'type' => 'dropdown',
            ]

        ];
    }
    
    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/experience.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/experience.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/font-awesome.min.css");

        $instance = ExperienceModel::find($this->property('Instance'));

        $this->page['experienceXP'] = $this->property('XP');
        $this->page['experienceBonus'] = $this->property('experienceBonus');
        $this->page['experiencePenalties'] = $this->property('experiencePenalties');
        $this->page['maxXP'] = $instance->Maximum;
        $this->page['milestones'] = $instance->Milestones;
        $this->page['startDate'] = $instance->StartDate;
        $this->page['endDate'] = $instance->EndDate;
        $this->page['experienceGrade'] = $this->property('experienceGrade');
        $this->page['experienceSize'] = $instance->Size;
        $this->page['experienceAnimate'] = $instance->Animate;
    }

    public function getInstanceOptions()
    {
        $instances = ExperienceModel::where("id","!=","0")->get();

        $array_dropdown = ['0'=>'- select Experience Instance - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

}
<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Xylum\Models\ComponentRules;
use Delphinium\Xylum\Models\ComponentTypes;
use Delphinium\Blade\Classes\Data\DataSource;

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
        $milestones = Milestone::orderBy('points','asc')->where('experience_id','=',$instance->id)->get();
        $milestoneArr = array();
        foreach($milestones as $item)
        {
            $milestoneArr[] = $item->name;
        }
        
        $this->page['encouragement'] = json_encode($milestoneArr);
        $this->page['experienceXP'] = 300;//current points
        $this->page['experienceBonus'] = 40;
        $this->page['experiencePenalties'] = 10;
        $this->page['maxXP'] = $instance->total_points;//total points for this experience
        $this->page['milestones'] = $instance->num_milestones;
        $this->page['startDate'] = $instance->start_date;
        $this->page['endDate'] = $instance->end_date;
        $this->page['experienceGrade'] = 500;//?
        $this->page['experienceSize'] = $instance->size;
        $this->page['experienceAnimate'] = $instance->animate;
        
        $cType = ComponentTypes::where(array('type' => 'experience'))->first();
        $componentRules = ComponentRules::where(array('component_id' => $cType->id));
        
        //run rules
        $source = new DataSource(false);
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $userId = $_SESSION['userID'];
        $params = array('student_ids'=>$userId,
            '$all_assignments'=>true,
            'fresh_data'=>0,
            'prettyprint'=>1,
            'rg'=>'submissionstest');
        echo json_encode($source->getMultipleSubmissions($params));
        //calculated variables
        $this->page['milestone_status']= array(); 
    }

    public function getInstanceOptions()
    {
        $instances = ExperienceModel::all();

        $array_dropdown = ['0'=>'- select Experience Instance - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->name;
        }

        return $array_dropdown;
    }

}
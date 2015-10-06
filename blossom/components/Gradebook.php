<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Enums\ActionType;
use \DateTime;

class Gradebook extends ComponentBase
{
    public $roots;
    public function componentDetails()
    {
        return [
            'name'        => 'Gradebook',
            'description' => 'Displays student\'s grades'
        ];
    }

     public function defineProperties() {
        return [
            'experienceInstance' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance to display the student\'s bonus and penalties',
                'type' => 'dropdown',
            ]
        ];
    }
    
    public function getExperienceInstanceOptions()
    {
        $instances = ExperienceModel::all();

        if(count($instances)===0)
        {
            return $array_dropdown = ['0'=>'No instances available. Bonus/penalties won\'t appear in the gradebook'];
        }
        else
        {
            $array_dropdown = ['0'=>'- select Experience Instance - '];
            foreach ($instances as $instance)
            {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }
    
    public function onRun()
    {   
        $this->roots = new Roots();
        //GET ANALYTICS STUDENT DATA
        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false);
        
        //GET ASSIGNMENT GROUPS
        $req = new AssignmentGroupsRequest(ActionType::GET, true, null, false);
        $assignmentGroups = $this->roots->assignmentGroups($req);     //returns an eloquent collection   
        
        $result = array();
        //Create a single array with the data we need
        foreach($assignmentGroups as $group)
        {
            $wrap = new \stdClass();
            $wrap->group_name = $group->name;
            $wrap->content = array();
            foreach($group->assignments as $assignment)//loop through each assignment in the group
            {
//                //retrieve the corresponding assignment in $analytics ($group->assignment_id)
                $analyticsArr= $this->findAssignmentById(intval($assignment->assignment_id), $analytics);
                
                if(count($analyticsArr)>0)
                {
                    $analyticsObj = $analyticsArr[0];//just take the first one. There shouldn't be more than one anyway
                    
                    $obj = new \stdClass();
                    $obj->name = $assignment->name;
                    $obj->html_url = $assignment->html_url;
                    $obj->points_possible = $assignment->points_possible;
                    $obj->score = (isset($analyticsObj->submission))?($analyticsObj->submission->score):null;
                    $obj->max_score= $analyticsObj->max_score;
                    $obj->min_score= $analyticsObj->min_score;
                    $obj->first_quartile = $analyticsObj->first_quartile;
                    $obj->median = $analyticsObj->median;
                    $obj->third_quartile = $analyticsObj->third_quartile;
                    
                    array_push($wrap->content, $obj);
                    
                }
                else
                {
                    continue;
                }
            }
            
            $result[] = $wrap;
        }
        
        $this->page['data'] = json_encode($result);
        
        $bonusPenalties = $this->getBonusPenalties();
        $this->page['bonus'] = $bonusPenalties ===0? 0: round($bonusPenalties->bonus,2);
        $this->page['penalties'] = $bonusPenalties ===0? 0: round($bonusPenalties->penalties,2);
        
        $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/gradebook.css");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
//        $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot_d3.js");
    }
    
    private function getBonusPenalties()
    {
        $experienceComp = new ExperienceComponent();
        if((!is_null($this->property('experienceInstance')))&&($this->property('experienceInstance')>0))
        {
            return $experienceComp->calculateTotalBonusPenalties($this->property('experienceInstance'));
        }
        else
        {
            return 0;
        }
    }
    
    private function findAssignmentById($assignmentId, $analytics)
    {
        $filteredItems = array_values(array_filter($analytics, function($elem) use($assignmentId){
                    return $elem->assignment_id === $assignmentId;
                }));
        return $filteredItems;
    }

}
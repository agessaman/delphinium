<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Xylum\Models\ComponentRules;
use Delphinium\Xylum\Models\ComponentTypes;
use Delphinium\Blade\Classes\Data\DataSource;
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
            $wrap->content[] = array();
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
                    $obj->score = isset($analyticsObj->submission)?$analyticsObj->submission:null;
                    $obj->max_score= $analyticsObj->max_score;
                    $obj->min_score= $analyticsObj->min_score;
                    $obj->first_quartile = $analyticsObj->first_quartile;
                    $obj->median = $analyticsObj->median;
                    $obj->third_quartile = $analyticsObj->third_quartile;
                    
                    $wrap->content[] = $obj;
                    
                }
                else
                {
                    continue;
                }
            }
            
            $result[] = $wrap;
        }
        
        $this->page['data'] = $result;
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/font-awesome.min.css");
    }
    
    private function findAssignmentById($assignmentId, $analytics)
    {
        $filteredItems = array_values(array_filter($analytics, function($elem) use($assignmentId){
                    return $elem->assignment_id === $assignmentId;
                }));
        return $filteredItems;
    }

}
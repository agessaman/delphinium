<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Grade as GradeModel;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Blossom\Models\Milestone;
use \DateTime;

class Grade extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Grade',
            'description' => 'Calculates and displays current grade'
        ];
    }

    public function defineProperties() {
        return [
            'gradeInstance' => [
                'title' => 'Instance',
                'description' => 'Select the Grade instance',
                'type' => 'dropdown',
            ],
            'experienceInstance' => [
                'title' => 'Experience instance',
                'description' => 'Select the experience instance. If one is provided, the grade calculation will include bonus and '
                . 'penalties. If none are available the grade will be pulled from Canvas',
                'type' => 'dropdown',
            ]
        ];
    }

    public function onRun() 
    {
        $instance = GradeModel::find($this->property('gradeInstance'));
        
        if(!is_null($this->property('experienceInstance')))
        {
            $experienceInstance = ExperienceModel::find($this->property('experienceInstance'));    
            
            $exComp = new ExperienceComponent();
            
             //set class variables
            $stDate = $experienceInstance->start_date;
            $endDate = $experienceInstance->end_date;
            
            $ptsPerSecond = $exComp->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
            $exComp->setPtsPerSecond($ptsPerSecond);
            $exComp->setStartDate($stDate);
            $exComp->setBonusPerSecond($experienceInstance->bonus_per_day/24/60/60);
            $exComp->setBonusSeconds($experienceInstance->bonus_days*24*60*60);
            $exComp->setPenaltyPerSecond($experienceInstance->penalty_per_day/24/60/60);
            $exComp->setPenaltySeconds($experienceInstance->penalty_days*24*60*60);
            
            $points = $exComp->getUserPoints();
            
            $milestonesDesc = Milestone::where('experience_id','=',$experienceInstance->id)->orderBy('points','desc')->get();
            $mileClearance = $exComp->getMilestoneClearanceInfo($milestonesDesc);
            
            $bonusPenalties = 0;
            foreach($mileClearance as $item)
            {
                if(($item->cleared))
                {
                    $bonusPenalties = $bonusPenalties+$item->bonusPenalty;
                }
            }
            
            $this->page['XP'] = round($points,2);
            $this->page['gradeBonus'] = round($bonusPenalties,2);
        }
        else
        {
            $this->page['XP'] = 0;
            $this->page['gradeBonus'] = 0;
        }
        
       
        
        //todo: get the bonus, etc from blade, not from experience
        $this->page['gradeSize'] = $instance->Size;

        $this->getGradeData();
        
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/grade.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/animate.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/grade.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
    }

    public function getGradeInstanceOptions() {
        $instances = GradeModel::where("id", "!=", "0")->get();

        $array_dropdown = ['0' => '- select Grade Instance - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

    public function getExperienceInstanceOptions()
    {
        $instances = ExperienceModel::all();

        if(count($instances)===0)
        {
            return $array_dropdown = ['0'=>'No instances available. Grade will be the same as in Canvas'];
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
    
    private function getGradeData() {
        $jsonData = '{
			"title": "New standard name",
			"id": 1,
			"context_id": 1,
			"context_type": "Course",
			"grading_scheme": [
				{"name": "A+", "value": 9700},
				{"name": "A", "value": 9500},
				{"name": "A-", "value": 9000},
				{"name": "B+", "value": 8700},
				{"name": "B", "value": 8400},
				{"name": "B-", "value": 8100},
				{"name": "C+", "value": 7700},
				{"name": "C", "value": 7400},
				{"name": "C-", "value": 7000},
				{"name": "D+", "value": 6700},
				{"name": "D", "value": 6400},
				{"name": "D-", "value": 6000},
				{"name": "F Keep going", "value": 0}

			]
		}';

        $gradeData = json_decode($jsonData);

        $this->page['apValue'] = $gradeData->grading_scheme[0]->value;
        $this->page['apName'] = $gradeData->grading_scheme[0]->name;
        $this->page['aValue'] = $gradeData->grading_scheme[1]->value;
        $this->page['aName'] = $gradeData->grading_scheme[1]->name;
        $this->page['amValue'] = $gradeData->grading_scheme[2]->value;
        $this->page['amName'] = $gradeData->grading_scheme[2]->name;
        $this->page['bpValue'] = $gradeData->grading_scheme[3]->value;
        $this->page['bpName'] = $gradeData->grading_scheme[3]->name;
        $this->page['bValue'] = $gradeData->grading_scheme[4]->value;
        $this->page['bName'] = $gradeData->grading_scheme[4]->name;
        $this->page['bmValue'] = $gradeData->grading_scheme[5]->value;
        $this->page['bmName'] = $gradeData->grading_scheme[5]->name;
        $this->page['cpValue'] = $gradeData->grading_scheme[6]->value;
        $this->page['cpName'] = $gradeData->grading_scheme[6]->name;
        $this->page['cValue'] = $gradeData->grading_scheme[7]->value;
        $this->page['cName'] = $gradeData->grading_scheme[7]->name;
        $this->page['cmValue'] = $gradeData->grading_scheme[8]->value;
        $this->page['cmName'] = $gradeData->grading_scheme[8]->name;
        $this->page['dpValue'] = $gradeData->grading_scheme[9]->value;
        $this->page['dpName'] = $gradeData->grading_scheme[9]->name;
        $this->page['dValue'] = $gradeData->grading_scheme[10]->value;
        $this->page['dName'] = $gradeData->grading_scheme[10]->name;
        $this->page['dmValue'] = $gradeData->grading_scheme[11]->value;
        $this->page['dmName'] = $gradeData->grading_scheme[11]->name;
        $this->page['fValue'] = $gradeData->grading_scheme[12]->value;
        $this->page['fName'] = $gradeData->grading_scheme[12]->name;
    }

}

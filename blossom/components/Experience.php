<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Xylum\Models\ComponentRules;
use Delphinium\Xylum\Models\ComponentTypes;
use Delphinium\Blade\Classes\Data\DataSource;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use \DateTime;

class Experience extends ComponentBase
{
    public $submissions;
    public $ptsPerDay;
    public $startDate;
    public $endDate;
    public $bonusPerDay;
    public $bonusDays;
    public $penaltyPerDay;
    public $penaltyDays;
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
        $milestones = Milestone::where('experience_id','=',$instance->id)->orderBy('points','asc')->get();
        $milestoneArr = array();
        foreach($milestones as $item)
        {
            $milestoneArr[] = $item->name;
        }
        
        //set class variables
        $stDate = new DateTime($instance->start_date);
        $endDate = new DateTime($instance->end_date);
        $this->startDate = $stDate;
        $this->endDate = $endDate;
        $this->submissions =$this->getSubmissions();
        $this->ptsPerDay = $this->getPtsPerDays($stDate, $endDate, $instance->total_points);
        $this->penaltyDays = $instance->penalty_days;
        $this->penaltyPerDay = $instance->penalty_per_day;
        $this->bonusDays = $instance->bonus_days;
        $this->bonusPerDay = $instance->bonus_per_day;
        
        //set page variables
        $this->page['encouragement'] = json_encode($milestoneArr);
        $this->page['experienceXP'] = $this->getUserPoints();//current points
        $this->page['experienceBonus'] = 40;
        $this->page['experiencePenalties'] = 10;
        $this->page['maxXP'] = $instance->total_points;//total points for this experience
        $this->page['milestones'] = $instance->num_milestones;
        $this->page['startDate'] = $instance->start_date;
        $this->page['endDate'] = $instance->end_date;
        $this->page['experienceGrade'] = 500;//?
        $this->page['experienceSize'] = $instance->size;
        $this->page['experienceAnimate'] = $instance->animate;
        
        $redLinePTs = $this->calculateRedLine($stDate, $endDate, $instance->total_points);
        $this->page['redLine'] = $redLinePTs;
        $mileInfo = $this->getMilestoneClearanceInfo($milestones);
        echo json_encode($mileInfo);
        
        /*
          
        //run rules
         
        $cType = ComponentTypes::where(array('type' => 'experience'))->first();
        $componentRules = ComponentRules::where(array('component_id' => $cType->id));
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
            'rg'=>'experience');
        echo json_encode($source->getMultipleSubmissions($params));
        //calculated variables
        $this->page['milestone_status']= array(); 
         * 
         */
         
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
    
    public function getUserPoints()
    {
        $score =0;
        foreach($this->submissions as $item)
        {
            $score = $score+intval($item['score']);
        }
        return $score;
    }
    
    private function getTotalPoints()
    {  
        if (!isset($_SESSION)) {
            session_start();
        }
        $roots = new Roots();
        $analytics = $roots->getAnalyticsStudentAssignmentData(false);
        return $analytics;
    }
    
    private function getMilestoneClearanceInfo($milestones)
    {
        //order submissions by date
        usort($this->submissions , function($a, $b) {
            $ad = new DateTime($a['submitted_at']);
            $bd = new DateTime($b['submitted_at']);

            if ($ad == $bd) {
              return 0;
            }

            return $ad > $bd ? 1 : -1;
          });

        $milestoneInfo = array();
        $carryingScore=0;
        foreach($this->submissions as $submission)
        {
            
            $carryingScore = $carryingScore+intval($submission['score']);
            foreach($milestones as $mile)
            {
                $mileClearance = new \stdClass();
                $mileClearance->milestone_id = $mile->id; 
                
                if($carryingScore>=$mile->points)//milestone cleared
                {
                    $mileClearance->cleared = 1;
                    $mileClearance->date = $submission['submitted_at'];
                    $mileClearance->bonus = $this->calculateBonus($mile->points, new DateTime($submission['submitted_at']));
                    $mileClearance->penalty = $this->calculatePenalty($mile->points, new DateTime($submission['submitted_at']));
                }
                else
                {
                    $mileClearance->cleared = 0;
                    $mileClearance->date = null;
                    $mileClearance->bonus = 0;
                    $mileClearance->penalty = 0;
                }
                $milestoneInfo[] = $mileClearance;
            }   
        }
        return $milestoneInfo;
    }
     
    private function getSubmissions()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $userId = $_SESSION['userID'];
        $roots = new Roots();
        $request = new SubmissionsRequest(ActionType::GET, array($userId), false, array(), true, false, true, false);
        return  $roots->submissions($request);
    }
    
    private function calculateRedLine(DateTime $startDate, DateTime $endDate, $totalPoints)
    {//-Red line = current day (in points)
        $ptsPerDay = $this->getPtsPerDays($startDate, $endDate, $totalPoints);
        $currentDays = $startDate->diff(new DateTime('now'));
        return floor($ptsPerDay*$currentDays->days);
    }
    
    
    private function calculateBonus($milestonePoints, $submittedAt)
    {
        $daysTranspired = ceil($milestonePoints/$this->ptsPerDay);
        $intervalDays = "P".$daysTranspired."D";
        $dueDate = $this->startDate->add(new \DateInterval($intervalDays));
        $daysDiff = $this->startDate->diff($this->endDate)->days;
        if($dueDate>$submittedAt)
        {//bonus
            $bonusDays = ($daysDiff>$this->bonusDays) ? $this->bonusDays : $daysDiff;
            return $bonusDays * $this->bonusPerDay;
        }
        else {return 0;}
    }
    private function calculatePenalty($milestonePoints, $submittedAt)
    {
        $daysTranspired = ceil($milestonePoints/$this->ptsPerDay);
        $intervalDays = "P".$daysTranspired."D";
        $dueDate = $this->startDate->add(new \DateInterval($intervalDays));
        $daysDiff = $this->startDate->diff($this->endDate)->days;
        if($dueDate<$submittedAt)
        {///penalty
            $penaltyDays = ($daysDiff>$this->penaltyDays)? $this->penaltyDays: $daysDiff;
            return $penaltyDays * $this->penaltyPerDay;
        }
        else {return 0;}
    }
    private function getPtsPerDays(DateTime $startDate, DateTime $endDate, $totalPoints)
    {
        $interval = $startDate->diff($endDate);
        return $totalPoints/$interval->days;
    }
    
}
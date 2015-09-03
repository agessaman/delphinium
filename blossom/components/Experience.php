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
    public $roots;
    public $submissions;
    public $ptsPerSecond;
    public $startDate;
    public $endDate;
    public $bonusPerSecond;
    public $bonusSeconds;
    public $penaltyPerSecond;
    public $penaltySeconds;
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
        try
        {
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/experience.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/experience.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/font-awesome.min.css");

            $this->roots = new Roots();
            $instance = ExperienceModel::find($this->property('Instance'));
            $milestonesDesc = Milestone::where('experience_id','=',$instance->id)->orderBy('points','desc')->get();
            $milestonesAsc = Milestone::where('experience_id','=',$instance->id)->orderBy('points','asc')->get();

            $milestoneArr = array();
            foreach($milestonesAsc as $item)
            {
                $milestoneArr[] = $item->name;
            }

            //set class variables
            $stDate = new DateTime($instance->start_date);
            $endDate = new DateTime($instance->end_date);
            $this->startDate = $stDate;
            $this->endDate = $endDate;
            $this->submissions =$this->getSubmissions();
            $this->ptsPerSecond = $this->getPtsPerSecond($stDate, $endDate, $instance->total_points);
            $this->penaltySeconds = $instance->penalty_days*24*60*60;
            $this->penaltyPerSecond = $instance->penalty_per_day/24/60/60;//convert it to milliseconds
            $this->bonusSeconds = $instance->bonus_days*24*60*60;
            $this->bonusPerSecond = $instance->bonus_per_day/24/60/60;

            //set page variables
            $this->page['encouragement'] = json_encode($milestoneArr);
            $this->page['experienceXP'] = $this->getUserPoints();//current points
            $this->page['maxXP'] = $instance->total_points;//total points for this experience
            $this->page['startDate'] = $instance->start_date;
            $this->page['endDate'] = $instance->end_date;
            $this->page['experienceSize'] = $instance->size;
            $this->page['experienceAnimate'] = $instance->animate;
            $redLine = $this->calculateRedLine($stDate, $endDate, $instance->total_points);
            $this->page['redLine'] = $redLine;
            $milestoneClearanceInfo = $this->getMilestoneClearanceInfo($milestonesDesc);
            $this->page['milestoneClearance'] = json_encode($milestoneClearanceInfo);
            $this->page['studentScores'] = json_encode($this->getAllStudentScores());

            $bonus=0;
            $penalties=0;
            foreach($milestoneClearanceInfo as $item)
            {
                if($item->bonusPenalty>0)
                {
                    $bonus = $bonus+$item->bonusPenalty;
                }
                else
                {
                    $penalties = $penalties+$item->bonusPenalty;
                }
            }
            $this->page['experienceGrade'] =  floor($this->getUserPoints()+$bonus+$penalties);//?
            $this->page['experienceBonus'] = $bonus;
            $this->page['experiencePenalties'] = $penalties;
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

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            echo "You must be a student to use this app, or go into 'Student View'. "
            . "Also, make sure that an Instructor has approved this application";
            return;
        }
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
        if(is_null($this->roots))
        {
            $this->roots = new Roots();
        }
        
        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false);
        return $analytics;
    }
    
    private function getMilestoneClearanceInfo($milestones)
    {
        $localMilestones = $milestones;
        //order submissions by date
        usort($this->submissions, function($a, $b) {
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
            foreach($localMilestones as $key=>$mile)
            {
                
                if($carryingScore>=$mile->points)//milestone cleared
                {
                    $mileClearance = new \stdClass();
                    $mileClearance->milestone_id = $mile->id; 
                    $mileClearance->cleared = 1;
                    $mileClearance->cleared_at = $submission['submitted_at'];
                    $mileClearance->bonusPenalty = $this->calculateBonusOrPenalty($mile->points, new DateTime($submission['submitted_at']));
//                    $mileClearance->penalty = $this->calculatePenalty($mile->points, new DateTime($submission['submitted_at']));
                    $milestoneInfo[] = $mileClearance;
                    unset($localMilestones[$key]);
                    
                }
            }   
        }
        
        foreach($localMilestones as $left)
        {//for the milestones that were left
            $mileClearance = new \stdClass();
            $mileClearance->milestone_id = $left->id; 
            $mileClearance->cleared = 0;
            $mileClearance->cleared_at = null;
            $mileClearance->bonusPenalty = 0;
            $milestoneInfo[] = $mileClearance;
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
        $ptsPerSecond = $this->getPtsPerSecond($startDate, $endDate, $totalPoints);
        $now = (new DateTime('now'));
//        $now->add(new \DateInterval("P1D"));
        $currentSeconds = abs($now->getTimestamp() - $startDate->getTimestamp());
        return floor($ptsPerSecond*$currentSeconds);
    }
    
    private function calculateBonusOrPenalty($milestonePoints, $submittedAt)
    {
        $secsTranspired = ceil($milestonePoints/$this->ptsPerSecond);
        $intervalSeconds = "PT".$secsTranspired."S";
        $sDate = $this->startDate;
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));
        $diffSeconds = abs($dueDate->getTimestamp() - $submittedAt->getTimestamp());
        
        if($dueDate>$submittedAt)
        {//bonus
            $bonusSeconds = ($diffSeconds>$this->bonusSeconds) ? $this->bonusSeconds : $diffSeconds;
            return $bonusSeconds * $this->bonusPerSecond;
        }
        else if ($dueDate<$submittedAt)
        {//bonus
            $penaltySeconds = ($diffSeconds>$this->penaltySeconds)? $this->penaltySeconds: $diffSeconds;
            return -($penaltySeconds * $this->penaltyPerSecond);
        }
        else    
        {//neither
            return 0;
        }
    }
//    private function calculateBonus()
//    {
//        $secsTranspired = ceil($milestonePoints/$this->ptsPerSecond);
//        $intervalSeconds = "PT".$secsTranspired."S";
//        $sDate = $this->startDate;
//        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));
//        
//        if($dueDate>$submittedAt)
//        {//bonus
//            $diffSeconds = abs($dueDate->getTimestamp() - $submittedAt->getTimestamp());
//            $bonusSeconds = ($diffSeconds>$this->bonusSeconds) ? $this->bonusSeconds : $diffSeconds;
//            return $bonusSeconds * $this->bonusPerSecond;
//        }
//        else {return 0;}
//    }
//    private function calculatePenalty($milestonePoints, $submittedAt)
//    {
//        $daysTranspired = ceil($milestonePoints/$this->ptsPerSecond);
//        $intervalDays = "P".$daysTranspired."D";
//        $sDate = $this->startDate;
//        $dueDate = $sDate->add(new \DateInterval($intervalDays));
//        
//        $diffMseconds = $this->startDate->diff($this->endDate)->days;
//        if($dueDate<$submittedAt)
//        {///penalty
//            $penaltyDays = ($diffMseconds>$this->penaltySeconds)? $this->penaltySeconds: $diffMseconds;
//            return $penaltyDays * $this->penaltyPerSecond;
//        }
//        else {return 0;}
//    }
    private function getPtsPerSecond(DateTime $startDate, DateTime $endDate, $totalPoints)
    {
        $intervalSeconds = abs($startDate->getTimestamp() - $endDate->getTimestamp());
        return $totalPoints/$intervalSeconds;
    }
    
    private function getAllStudentScores()
    {
        if(is_null($this->roots))
        {
            $this->roots = new Roots();
        }
        $req = new SubmissionsRequest(ActionType::GET, array(), true, array(), true, true, true, false, true);
        $res = $this->roots->submissions($req);
        
        $scores = array();
        $score = 0;
        $userId = 0;
        for($i=0;$i<=count($res)-1;$i++)
        {
            $submission = $res[$i];
            if($userId===$submission['user_id'])
            {
                $score = $score+$submission['score'];
            }
            else if($userId!==0)
            {
                $scores[] = $score;
                $score = $submission['score'];
            }
            else
            {
                $score = $score+$submission['score'];
            }
            $userId = $submission['user_id'];
            if($i===count($res)-1)
            {//add last item
                $scores[] = $score;
            }
        }
        return $scores;
    }
}
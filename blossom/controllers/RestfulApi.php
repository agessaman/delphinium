<?php namespace Delphinium\Blossom\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use \DateTime;
use DateTimeZone;

class RestfulApi extends Controller 
{
    private $roots;
    private $instance;
    private $submissions;
    private $ptsPerSecond;
    private $startDate;
    private $endDate;
    private $bonusPerSecond;
    private $bonusSeconds;
    private $penaltyPerSecond;
    private $penaltySeconds;
    
    public function getMilestoneClearanceInfo()
    {
        $this->submissions = $this->getSubmissions();
        $instanceId = \Input::get('experienceInstanceId');
        
        $this->instance = ExperienceModel::find($instanceId);
        
        $stDate = $this->instance->start_date;
        $endDate = $this->instance->end_date;
        $this->startDate = $stDate;
        $this->endDate = $endDate;
        
        $this->ptsPerSecond = $this->getPtsPerSecond($stDate, $endDate, $this->instance->total_points);
        $this->penaltySeconds = $this->instance->penalty_days*24*60*60;
        $this->penaltyPerSecond = $this->instance->penalty_per_day/24/60/60;//convert it to milliseconds
        $this->bonusSeconds = $this->instance->bonus_days*24*60*60;
        $this->bonusPerSecond = $this->instance->bonus_per_day/24/60/60;
        
        $localMilestones = Milestone::where('experience_id','=',$instanceId)->orderBy('points','desc')->get();

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
                    $mileClearance->name = $mile->name;
                    $mileClearance->cleared = 1;
                    $mileClearance->cleared_at = $submission['submitted_at'];
                    $mileClearance->bonusPenalty = $this->calculateBonusOrPenalty($mile->points, new DateTime($submission['submitted_at']));
                    $mileClearance->points = $mile->points;
                    $mileClearance->due_at = $this->calculateMilestoneDueDate($mile->points);
                    $milestoneInfo[] = $mileClearance;
                    unset($localMilestones[$key]);
                    
                }
            }   
        }
        
        //sort the remaining milestones by points asc
        $mileArray = $localMilestones->toArray();
        usort($mileArray, function($a, $b) {
            $ad = $a['points'];
            $bd = $b['points'];

            if ($ad == $bd) {
              return 0;
            }

            return $ad > $bd ? 1 : -1;
        });
        
        foreach($mileArray as $left)
        {//for the milestones that were left
            $mileClearance = new \stdClass();
            $mileClearance->milestone_id = $left['id'];
            $mileClearance->name = $left['name'];
            $mileClearance->cleared = 0;
            $mileClearance->cleared_at = null;
            $now = new DateTime('now',new DateTimeZone('UTC'));
            $mileClearance->bonusPenalty = $this->calculateBonusOrPenalty($left['points'], $now);
            $mileClearance->points = $left['points'];
            
            $date = $this->calculateMilestoneDueDate($left['points']);
            
            $mileClearance->due_at = $date;
            $milestoneInfo[] = $mileClearance;
        }
        return $milestoneInfo;
    }
    
    
    public function getStudentsScores()
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
    
    private function getUserPoints()
    {
        $score =0;
        if(is_null($this->submissions))
        {
            $this->submissions = $this->getSubmissions();
        }
        
        foreach($this->submissions as $item)
        {
            $score = $score+intval($item['score']);
        }
        return $score;
    }
    
    private function getSubmissions()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $userId = $_SESSION['userID'];
        $this->roots = new Roots();
        $request = new SubmissionsRequest(ActionType::GET, array($userId), false, array(), true, false, true, false);
        $submissions =  $this->roots->submissions($request);
        return $submissions;
    }
    
    private function calculateBonusOrPenalty($milestonePoints, $submittedAt)//submittedAt will also be in UTC
    {
        $secsTranspired = ceil($milestonePoints/$this->ptsPerSecond);
        $intervalSeconds = "PT".$secsTranspired."S";
        $sDate = clone($this->startDate);//this start date is in UTC
        
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
    
    private function calculateMilestoneDueDate($milestonePoints)
    {
        $secsTranspired = ceil($milestonePoints/$this->ptsPerSecond);
        $intervalSeconds = "PT".$secsTranspired."S";
        
        $sDate = clone($this->startDate);
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));
        
        return $dueDate;//this is in UTC!
    }
    
    public function getPtsPerSecond(DateTime $startDate, DateTime $endDate, $totalPoints)
    {
        $intervalSeconds = abs($startDate->getTimestamp() - $endDate->getTimestamp());
        return $totalPoints/$intervalSeconds;
    }
}

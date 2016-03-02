<?php namespace Delphinium\Blossom\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Blossom\Components\Experience as ExperienceController;
use Delphinium\Blossom\Components\Gradebook as GradebookComponent;
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

        try{
            $this->submissions = $this->getSubmissions();
            $instanceId = \Input::get('experienceInstanceId');

            $expController = new ExperienceController();
            $milestoneInfo = $expController->getMilestoneClearanceInfo($instanceId);

            return $milestoneInfo;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return[];
        }
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
            $score = $score+floatval($item['score']);
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

    public function getStudentGradebookData()
    {
        $studentId = (!is_null(\Input::get('studentId')))?\Input::get('studentId'):null;
        $gradebook = new GradebookComponent();
        return $gradebook->getStudentData(true, $studentId);
    }

    public function getGradeData()
    {
        $gradebook = new GradebookComponent();
        $bonusPenalties = $gradebook->getBonusPenalties();
    }

    public function getTotalUserPoints()
    {
        $exp = new ExperienceComponent();
        $pts = $exp->getUserPoints();
    }


    public function getStudentChartData()
    {
        $studentId = (!is_null(\Input::get('studentId')))?\Input::get('studentId'):null;

        $gradebook = new GradebookComponent();
        return $gradebook->getStudentChartData($studentId);
    }

    public function getAllStudentSubmissions()
    {
        $gradebook = new GradebookComponent();
        return $gradebook->aggregateSubmissionScores();
    }

    public function getAllUserClearedMilestoneData()
    {
        $instanceId = \Input::get('experienceInstanceId');
        $gradebook = new GradebookComponent();
        return $gradebook->getAllUserClearedMilestoneData($instanceId);
    }
    public function getSetOfUsersMilestoneInfo()
    {
        $instanceId = \Input::get('experienceInstanceId');
        $userIds = \Input::get('userIds');

        $gradebook = new GradebookComponent();
        return $gradebook->getSetOfUsersMilestoneInfo($instanceId, $userIds);
    }


    public function getSetOfUsersTotalScores()
    {
        $instanceId = \Input::get('experienceInstanceId');
        $userIds =\Input::get('userIds');

        $gradebook = new GradebookComponent();

        return $gradebook->getSetOfUsersTotalScores($instanceId, $userIds);
    }
}

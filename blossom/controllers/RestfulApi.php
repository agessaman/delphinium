<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Blossom\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Blossom\Components\Experience as ExperienceController;
use Delphinium\Blossom\Components\Gradebook as GradebookComponent;
use Delphinium\Blossom\Components\Stats as StatsComponent;
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
        try{
            $submissions =  $this->roots->submissions($request);
            return $submissions;
        }
        catch (\Exception $e)
        {
            trace_log($e);
            return [];
        }

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

    public function getStudentSubmission()
    {
        $gradebook = new GradebookComponent();
        return $gradebook->aggregateSubmissionStudentScores();
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

    public function getStatsData()
    {
        $instanceId = \Input::get('experienceInstanceId');
        $stats = new StatsComponent();
        $data = $stats->getStatsData($instanceId);
        return json_encode($data);
    }

    public function sendEmailInCourse()
    {
        $gradebook = new GradebookComponent();
        $id = \Input::get('id');
        $subject = \Input::get('subject');
        $message = \Input::get('message');

        return $gradebook->sendEmailInCourse($id, $subject, $message);
    }
}

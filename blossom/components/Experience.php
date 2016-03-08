<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Models\Milestone;
use Delphinium\Xylum\Models\ComponentRules;
use Delphinium\Xylum\Models\ComponentTypes;
use Delphinium\Blade\Classes\Data\DataSource;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Utils;
use \DateTime;
use \DateTimeZone;
use \Delphinium\Roots\Exceptions\InvalidRequestException;

class Experience extends ComponentBase {

    public $roots;
    public $submissions;
    public $ptsPerSecond;
    public $startDateUTC;
    public $endDateUTC;
    public $bonusPerSecond;
    public $bonusSeconds;
    public $penaltyPerSecond;
    public $penaltySeconds;
    public $instance;

    function setSubmissions($submissions) {
        $this->submissions = $submissions;
    }

    function setPtsPerSecond($ptsPerSecond) {
        $this->ptsPerSecond = $ptsPerSecond;
    }

    function getRoots() {
        return $this->roots;
    }

    function getStartDate() {
        return $this->startDateUTC;
    }

    function getEndDate() {
        return $this->endDateUTC;
    }

    function getBonusPerSecond() {
        return $this->bonusPerSecond;
    }

    function getBonusSeconds() {
        return $this->bonusSeconds;
    }

    function getPenaltyPerSecond() {
        return $this->penaltyPerSecond;
    }

    function getPenaltySeconds() {
        return $this->penaltySeconds;
    }

    function setRoots($roots) {
        $this->roots = $roots;
    }

    function setStartDateUTC($startDate) {
        $this->startDateUTC = $startDate;
    }

    function setEndDateUTC($endDate) {
        $this->endDateUTC = $endDate;
    }

    function setBonusPerSecond($bonusPerSecond) {
        $this->bonusPerSecond = $bonusPerSecond;
    }

    function setBonusSeconds($bonusSeconds) {
        $this->bonusSeconds = $bonusSeconds;
    }

    function setPenaltyPerSecond($penaltyPerSecond) {
        $this->penaltyPerSecond = $penaltyPerSecond;
    }

    function setPenaltySeconds($penaltySeconds) {
        $this->penaltySeconds = $penaltySeconds;
    }

    public function componentDetails() {
        return [
            'name' => 'Experience',
            'description' => 'Displays students experience'
        ];
    }

    public function defineProperties() {
        return [

            'Instance' => [
                'title' => 'Instance',
                'description' => 'Select the Experience instance',
                'type' => 'dropdown',
            ]
        ];
    }

    public function onRun() {//this.startDate and this.endDate are in UTC. The instance in the model will remain in the user's timezone
        try {
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/experience.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/experience.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");

            $this->roots = new Roots();
            $instance = ExperienceModel::find($this->property('Instance'));
            $this->instance = $instance;

            //set class variables
            $utcTimeZone = new DateTimeZone('UTC');
            $stDate = $instance->start_date->setTimezone($utcTimeZone);
            $endDate = $instance->end_date->setTimezone($utcTimeZone);
            $this->startDateUTC = $stDate;
            $this->endDateUTC = $endDate;
            $this->submissions = $this->getSubmissions();
            $this->ptsPerSecond = $this->getPtsPerSecond($stDate, $endDate, $instance->total_points);
            $this->penaltySeconds = $instance->penalty_days * 24 * 60 * 60;
            $this->penaltyPerSecond = $instance->penalty_per_day / 24 / 60 / 60; //convert it to milliseconds
            $this->bonusSeconds = $instance->bonus_days * 24 * 60 * 60;
            $this->bonusPerSecond = $instance->bonus_per_day / 24 / 60 / 60;

            $this->page['bonusDays'] = $instance->bonus_days;
            $this->page['maxBonus'] = $this->bonusSeconds * $this->bonusPerSecond;
            //set page variables
            $this->page['instanceId'] = $instance->id;
            $this->page['experienceXP'] = $this->getUserPoints(); //current points
            $this->page['maxXP'] = $instance->total_points; //total points for this experience
            $this->page['experienceSize'] = $instance->size;
            $this->page['experienceAnimate'] = $instance->animate;
            $this->page['redLine'] = $this->getRedLinePoints($this->property('Instance'));

        }
        catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
        {
            if($e->getCode()==401)//meaning there are two professors and one is trying to access the other professor's grades
            {
                return;
            }
            else
            {
                return \Response::make($this->controller->run('error'), 500);
            }
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            echo "In order for experience to work properly you must be a student, or go into 'Student View'";
            return;
        }
        catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        {
            if($e->getCode()==584)
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
        }
        catch(\Exception $e)
        {
            if($e->getMessage()=='Invalid LMS')
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
    }

    public function getRedLinePoints($experienceInstanceId) {//only deal with UTC dates. The model will return the date in the user's timezone, but we'll convert it to UTC
        $instance = ExperienceModel::find($experienceInstanceId);

        $utcTimeZone = new DateTimeZone('UTC');
        $now = new DateTime('now', $utcTimeZone);
        $startDateUTC = $instance->start_date->setTimezone($utcTimeZone);
        $endDateUTC = $instance->end_date->setTimezone($utcTimeZone);
        $currentSeconds = abs($now->getTimestamp() - $startDateUTC->getTimestamp());

        $this->ptsPerSecond = $this->getPtsPerSecond($startDateUTC, $endDateUTC, $instance->total_points);

        if($startDateUTC > $now)
        {
            return 0;
        }
        return floor($this->ptsPerSecond * $currentSeconds);
    }

    public function getInstanceOptions() {
        $instances = ExperienceModel::all();

        $array_dropdown = ['0' => '- select Experience Instance - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->name;
        }

        return $array_dropdown;
    }

    public function getUserPoints() {
        $score = 0;
        if (is_null($this->submissions)) {
            $this->submissions = $this->getSubmissions();
        }

        foreach ($this->submissions as $item) {
            $score = $score + floatval($item['score']);
        }
        return $score;
    }

    private function getTotalPoints() {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }

        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false);
        return $analytics;
    }

    public function getMilestonesOrderedByPointsDesc($experienceInstanceId)
    {
        $localMilestones = ExperienceModel::with(array('milestones' =>
            function($query) {
                $query->orderBy('points', 'DESC');
            }))
            ->where(array(
                'id' => $experienceInstanceId
            ))->first()->milestones;
        $expInstance = ExperienceModel::find($experienceInstanceId);
        $utcTimeZone = new DateTimeZone('UTC');
        $endDateUTC = $expInstance->end_date->setTimezone($utcTimeZone);

        return $localMilestones;

    }

    public function userClearedMilestones($milestonesOrderedByPointsDesc, $userSubmissionsOrderedByDate, $ptsPerSecond, $stDateUTC,$endDateUTC, $bonusPerSecond, $bonusSeconds,
                                          $penaltyPerSecond, $penaltySeconds)
    {
        $localMilestonesOrderedByPointsDesc = clone $milestonesOrderedByPointsDesc;
        $milestoneInfo = array();
        $carryingScore = 0;
        foreach ($userSubmissionsOrderedByDate as $submission) {
            $carryingScore = $carryingScore + floatval($submission['score']);
            foreach ($localMilestonesOrderedByPointsDesc as $key => $mile) {

                if ($carryingScore >= $mile->points) {//milestone cleared
                    $mileClearance = new \stdClass();
                    $mileClearance->milestone_id = $mile->id;
                    $mileClearance->name = $mile->name;
                    $mileClearance->cleared = 1;
                    $mileClearance->cleared_at = $submission['submitted_at'];
                    $mileClearance->bonusPenalty = $this->calculateBonusOrPenaltyNew($mile->points, new DateTime($submission['submitted_at']), $endDateUTC, true,
                        $ptsPerSecond, $stDateUTC, $bonusSeconds, $bonusPerSecond, $penaltySeconds, $penaltyPerSecond);
                    $mileClearance->points = $mile->points;
                    $mileClearance->due_at = $this->calculateMilestoneDueDateNew($mile->points, $ptsPerSecond, $stDateUTC);
                    $milestoneInfo[] = $mileClearance;
                    unset($localMilestonesOrderedByPointsDesc[$key]);
                }
            }
        }

        //sort the remaining milestones by points asc
        $mileArray = $localMilestonesOrderedByPointsDesc->toArray();
        usort($mileArray, function($a, $b) {
            $ad = $a['points'];
            $bd = $b['points'];

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });

        foreach ($mileArray as $left) {//for the milestones that were left
            $mileClearance = new \stdClass();
            $mileClearance->milestone_id = $left['id'];
            $mileClearance->name = $left['name'];
            $mileClearance->cleared = 0;
            $mileClearance->cleared_at = null;
            $mileClearance->bonusPenalty = $this->calculateBonusOrPenaltyNew($left['points'], new DateTime('now'), $endDateUTC, false,
                $ptsPerSecond, $stDateUTC, $bonusSeconds, $bonusPerSecond, $penaltySeconds, $penaltyPerSecond);
            $mileClearance->points = $left['points'];

            $mileClearance->due_at = $this->calculateMilestoneDueDateNew($left['points'], $ptsPerSecond, $stDateUTC);
            $milestoneInfo[] = $mileClearance;
        }
        return $milestoneInfo;
    }
    // We are overloading some classes because we need to optimize the code by doing some sort of dependency injection.
    public function getMilestoneClearanceInfoNew($experienceInstanceId, $ptsPerSecond, $stDateUTC, $bonusPerSecond, $bonusSeconds,
                                                 $penaltyPerSecond, $penaltySeconds, $userSubmissions)
    {
        $milestonesDesc = ExperienceModel::with(array('milestones' =>
            function($query) {
                $query->orderBy('points', 'DESC');
            }))
            ->where(array(
                'id' => $experienceInstanceId
            ))->first()->milestones;
        $expInstance = ExperienceModel::find($experienceInstanceId);
        $utcTimeZone = new DateTimeZone('UTC');
        $endDateUTC = $expInstance->end_date->setTimezone($utcTimeZone);

        $localMilestones = $milestonesDesc;

        //order submissions by date
        usort($userSubmissions, function($a, $b) {
            $ad = new DateTime($a['submitted_at']);
            $bd = new DateTime($b['submitted_at']);

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });
        $milestoneInfo = array();
        $carryingScore = 0;
        foreach ($userSubmissions as $submission) {
            $carryingScore = $carryingScore + floatval($submission['score']);
            foreach ($localMilestones as $key => $mile) {

                if ($carryingScore >= $mile->points) {//milestone cleared
                    $mileClearance = new \stdClass();
                    $mileClearance->milestone_id = $mile->id;
                    $mileClearance->name = $mile->name;
                    $mileClearance->cleared = 1;
                    $mileClearance->cleared_at = $submission['submitted_at'];
                    $mileClearance->bonusPenalty = $this->calculateBonusOrPenaltyNew($mile->points, new DateTime($submission['submitted_at']), $endDateUTC, true,
                        $ptsPerSecond, $stDateUTC, $bonusSeconds, $bonusPerSecond, $penaltySeconds, $penaltyPerSecond);
                    $mileClearance->points = $mile->points;
                    $mileClearance->due_at = $this->calculateMilestoneDueDateNew($mile->points, $ptsPerSecond, $stDateUTC);
                    $milestoneInfo[] = $mileClearance;
                    unset($localMilestones[$key]);
                }
            }
        }

        //sort the remaining milestones by points asc
        $mileArray = $milestonesDesc->toArray();
        usort($mileArray, function($a, $b) {
            $ad = $a['points'];
            $bd = $b['points'];

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });

        foreach ($mileArray as $left) {//for the milestones that were left
            $mileClearance = new \stdClass();
            $mileClearance->milestone_id = $left['id'];
            $mileClearance->name = $left['name'];
            $mileClearance->cleared = 0;
            $mileClearance->cleared_at = null;
            $mileClearance->bonusPenalty = $this->calculateBonusOrPenaltyNew($left['points'], new DateTime('now'), $endDateUTC, false,
                $ptsPerSecond, $stDateUTC, $bonusSeconds, $bonusPerSecond, $penaltySeconds, $penaltyPerSecond);
            $mileClearance->points = $left['points'];

            // $date = $this->calculateMilestoneDueDate($left['points']);
            $mileClearance->due_at = $this->calculateMilestoneDueDateNew($left['points'], $ptsPerSecond, $stDateUTC);
            $milestoneInfo[] = $mileClearance;
        }
        return $milestoneInfo;

    }
    public function getMilestoneClearanceInfo($experienceInstanceId, $userId = null) {
        $this->initVariables($experienceInstanceId, $userId);
        $milestonesDesc = ExperienceModel::with(array('milestones' =>
            function($query) {
                $query->orderBy('points', 'DESC');
            }))
            ->where(array(
                'id' => $experienceInstanceId
            ))->first()->milestones;
        $expInstance = ExperienceModel::find($experienceInstanceId);
        $utcTimeZone = new DateTimeZone('UTC');
        $endDateUTC = $expInstance->end_date->setTimezone($utcTimeZone);

        $localMilestones = $milestonesDesc;

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
        $carryingScore = 0;
        foreach ($this->submissions as $submission) {

            $carryingScore = $carryingScore + floatval($submission['score']);

            foreach ($localMilestones as $key => $mile) {

                if ($carryingScore >= $mile->points) {//milestone cleared
                    $mileClearance = new \stdClass();
                    $mileClearance->milestone_id = $mile->id;
                    $mileClearance->name = $mile->name;
                    $mileClearance->cleared = 1;
                    $mileClearance->cleared_at = $submission['submitted_at'];
                    $mileClearance->bonusPenalty = $this->calculateBonusOrPenalty($mile->points, new DateTime($submission['submitted_at']), $endDateUTC, true);
                    $mileClearance->points = $mile->points;
                    $mileClearance->due_at = $this->calculateMilestoneDueDate($mile->points);
                    $milestoneInfo[] = $mileClearance;
                    unset($localMilestones[$key]);
                }
            }
        }

        //sort the remaining milestones by points asc
        $mileArray = $milestonesDesc->toArray();
        usort($mileArray, function($a, $b) {
            $ad = $a['points'];
            $bd = $b['points'];

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });

        foreach ($mileArray as $left) {//for the milestones that were left
            $mileClearance = new \stdClass();
            $mileClearance->milestone_id = $left['id'];
            $mileClearance->name = $left['name'];
            $mileClearance->cleared = 0;
            $mileClearance->cleared_at = null;
            $mileClearance->bonusPenalty = $this->calculateBonusOrPenalty($left['points'], new DateTime('now'), $endDateUTC, false);
            $mileClearance->points = $left['points'];

            $date = $this->calculateMilestoneDueDate($left['points']);

            $mileClearance->due_at = $date;
            $milestoneInfo[] = $mileClearance;
        }
        return $milestoneInfo;
    }

    // We are overloading some classes because we need to optimize the code by doing some sort of dependency injection.
    public function calculateTotalBonusPenalties($experienceInstanceId, $userId = null) {

        $mileClearance = $this->getMilestoneClearanceInfo($experienceInstanceId, $userId);

        $obj = new \stdClass();
        $obj->bonus = 0;
        $obj->penalties = 0;

        foreach ($mileClearance as $item) {
            if (($item->cleared)) {
                if ($item->bonusPenalty > 0) {
                    $obj->bonus = $obj->bonus + $item->bonusPenalty;
                } else {
                    $obj->penalties = $obj->penalties + $item->bonusPenalty;
                }
            }
        }
        return $obj;
    }

    public function calculateTotalBonusPenaltiesNew($experienceInstanceId, $userSubmissions, $ptsPerSecond, $stDateUTC, $bonusPerSecond, $bonusSeconds,
                                                    $penaltyPerSecond, $penaltySeconds)
    {
        $mileClearance = $this->getMilestoneClearanceInfoNew($experienceInstanceId, $ptsPerSecond, $stDateUTC, $bonusPerSecond, $bonusSeconds,
            $penaltyPerSecond, $penaltySeconds, $userSubmissions);
        $obj = new \stdClass();
        $obj->bonus = 0;
        $obj->penalties = 0;

        foreach ($mileClearance as $item) {
            if (($item->cleared)) {
                if ($item->bonusPenalty > 0) {
                    $obj->bonus = $obj->bonus + $item->bonusPenalty;
                } else {
                    $obj->penalties = $obj->penalties + $item->bonusPenalty;
                }
            }
        }
        return $obj;
    }

    public function initVariables($experienceInstanceId, $userId = null) {//set class variables
        $experienceInstance = ExperienceModel::find($experienceInstanceId);

        $utcTimeZone = new DateTimeZone('UTC');
        $stDate = $experienceInstance->start_date->setTimezone($utcTimeZone);
        $endDate = $experienceInstance->end_date->setTimezone($utcTimeZone);

        $ptsPerSecond = $this->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
        $this->setPtsPerSecond($ptsPerSecond);
        $this->setStartDateUTC($stDate);
        $this->setBonusPerSecond($experienceInstance->bonus_per_day / 24 / 60 / 60);
        $this->setBonusSeconds($experienceInstance->bonus_days * 24 * 60 * 60);
        $this->setPenaltyPerSecond($experienceInstance->penalty_per_day / 24 / 60 / 60);
        $this->setPenaltySeconds($experienceInstance->penalty_days * 24 * 60 * 60);

        if (is_null($this->submissions)) {
            $this->submissions = $this->getSubmissions($userId);
        }
    }

    public function getSubmissions($userId = null) {
        if (is_null($userId)) {
            if (!isset($_SESSION)) {
                session_start();
            }

            $userId = $_SESSION['userID'];
        }

        $roots = new Roots();
        $request = new SubmissionsRequest(ActionType::GET, array($userId), false, array(), true, false, true, false);


        try
        {
            $submissions = $roots->submissions($request);
            return $submissions;
        } catch (\Delphinium\Roots\Exceptions\InvalidRequestException $e) {
            if($e->getCode()===401)
            {return [];}
            else{throw $e;}
        }

    }

    private function calculateRedLine(DateTime $startDateUTC, DateTime $endDateUTC, $totalPoints) {//-Red line = current day (in points)
        $ptsPerSecond = $this->getPtsPerSecond($startDateUTC, $endDateUTC, $totalPoints);
        $now = (new DateTime('now', new DateTimeZone('UTC')));

        $currentSeconds = abs($now->getTimestamp() - $startDateUTC->getTimestamp());

        return floor($ptsPerSecond * $currentSeconds);
    }

    // We are overloading some classes because we need to optimize the code by doing some sort of dependency injection.
    private function calculateMilestoneDueDate($milestonePoints) {
        $secsTranspired = ceil($milestonePoints / $this->ptsPerSecond);
        $intervalSeconds = "PT" . $secsTranspired . "S";

        $sDate = clone($this->startDateUTC);
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));

        //set to user's timezone
        $localDate = Utils::setLocalTimezone($dueDate);

        return $localDate;
    }

    private function calculateMilestoneDueDateNew($milestonePoints, $ptsPerSecond, $startDateUTC)
    {
        $secsTranspired = ceil($milestonePoints / $ptsPerSecond);
        $intervalSeconds = "PT" . $secsTranspired . "S";

        $sDate = clone($startDateUTC);
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));

        //set to user's timezone
        $localDate = Utils::setLocalTimezone($dueDate);

        return $localDate;
    }

    // We are overloading some classes because we need to optimize the code by doing some sort of dependency injection.
    private function calculateBonusOrPenalty($milestonePoints, $submittedAt, $endExperienceDateUTC, $turnedIn) {
        if(intval($milestonePoints)===0)
        {//If students complete their first assignment after the first milestone is due, even though the milestone is worth zero points,
            //if will consider it late and will give them penalty points.
            return 0;
        }
        $secsTranspired = ceil($milestonePoints / $this->ptsPerSecond);
        $intervalSeconds = "PT" . $secsTranspired . "S";
        $sDate = clone($this->startDateUTC);
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));
        $diffSeconds = abs($dueDate->getTimestamp() - $submittedAt->getTimestamp());

        if ($dueDate > $submittedAt) {//bonus
            $bonusSeconds = ($diffSeconds > $this->bonusSeconds) ? $this->bonusSeconds : $diffSeconds;
            return $bonusSeconds * $this->bonusPerSecond;
        } else if ($dueDate < $submittedAt) {//penalties
            $penaltySeconds = ($diffSeconds > $this->penaltySeconds) ? $this->penaltySeconds : $diffSeconds;
            //For assignments that have not been turned in, late days after the last day of experience should not count.
            //the penalties days should not go beyond the last day of experience, so it the due date for a milestone was one day before
            //the last day of experience, then the max penalties days should be one day
            if(!$turnedIn)
            {
                $secondsLate = "PT" . $penaltySeconds . "S";
                $todayPlusDaysLate = $submittedAt->add(new \DateInterval($secondsLate));
                if ($todayPlusDaysLate > $endExperienceDateUTC)
                {
                    $penaltySeconds = abs($todayPlusDaysLate->getTimestamp() - $endExperienceDateUTC->getTimestamp());
                }
            }
            return -($penaltySeconds * $this->penaltyPerSecond);
        } else {//neither
            return 0;
        }
    }

    private function calculateBonusOrPenaltyNew($milestonePoints, $submittedAt, $endExperienceDateUTC, $turnedIn, $ptsPerSecond,
                                                $startDateUTC, $inBonusSeconds, $inBonusPerSecond, $inPenaltySeconds, $inPenaltyPerSecond)
    {
        if(intval($milestonePoints)===0)
        {//If students complete their first assignment after the first milestone is due, even though the milestone is worth zero points,
            //if will consider it late and will give them penalty points.
            return 0;
        }
        $secsTranspired = ceil($milestonePoints / $ptsPerSecond);
        $intervalSeconds = "PT" . $secsTranspired . "S";
        $sDate = clone($startDateUTC);
        $dueDate = $sDate->add(new \DateInterval($intervalSeconds));
        $diffSeconds = abs($dueDate->getTimestamp() - $submittedAt->getTimestamp());

        if ($dueDate > $submittedAt) {//bonus
            $bonusSeconds = ($diffSeconds > $inBonusSeconds) ? $inBonusSeconds : $diffSeconds;
            return $bonusSeconds * $inBonusPerSecond;
        } else if ($dueDate < $submittedAt) {//penalties
            $penaltySeconds = ($diffSeconds > $inPenaltySeconds) ? $inPenaltySeconds : $diffSeconds;
            //For assignments that have not been turned in, late days after the last day of experience should not count.
            //the penalties days should not go beyond the last day of experience, so it the due date for a milestone was one day before
            //the last day of experience, then the max penalties days should be one day
            if(!$turnedIn)
            {
                $secondsLate = "PT" . $penaltySeconds . "S";
                $todayPlusDaysLate = $submittedAt->add(new \DateInterval($secondsLate));
                if ($todayPlusDaysLate > $endExperienceDateUTC)
                {
                    $penaltySeconds = abs($todayPlusDaysLate->getTimestamp() - $endExperienceDateUTC->getTimestamp());
                }
            }
            return -($penaltySeconds * $inPenaltyPerSecond);
        } else {//neither
            return 0;
        }
    }
    public function getPtsPerSecond(DateTime $UTCstartDate, DateTime $UTCendDate, $totalPoints) {
        $intervalSeconds = abs($UTCstartDate->getTimestamp() - $UTCendDate->getTimestamp());
        return $totalPoints / $intervalSeconds;
    }

    public function getAllStudentScores() {
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $req = new SubmissionsRequest(ActionType::GET, array(), true, array(), true, true, true, false, true);
        $res = $this->roots->submissions($req);

        $scores = array();
        $score = 0;
        $userId = 0;
        for ($i = 0; $i <= count($res) - 1; $i++) {
            $submission = $res[$i];
            if ($userId === $submission['user_id']) {
                $score = $score + $submission['score'];
            } else if ($userId !== 0) {
                $scores[] = $score;
                $score = $submission['score'];
            } else {
                $score = $score + $submission['score'];
            }
            $userId = $submission['user_id'];
            if ($i === count($res) - 1) {//add last item
                $scores[] = $score;
            }
        }
        return $scores;
    }

}

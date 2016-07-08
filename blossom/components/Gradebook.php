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

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Blossom\Components\Grade as GradeComponent;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Utils;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\DB\DbHelper;
use \DateTime;
use \DateTimeZone;
use \DateInterval;
use Carbon\Carbon;
use Delphinium\Roots\Guzzle\GuzzleHelper;
use Delphinium\Blossom\Components\Stats as StatsComponent;

class Gradebook extends ComponentBase {

    public $roots;
    public $studentData;
    public $users;
    public $submissions;
    public $allStudentSubmissions;

    public function componentDetails() {
        return [
            'name' => 'Gradebook',
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

    public function getExperienceInstanceOptions() {
        $instances = ExperienceModel::all();

        if (count($instances) === 0) {
            return $array_dropdown = ['0' => 'No instances available. Bonus/penalties won\'t appear in the gradebook'];
        } else {
            $array_dropdown = ['0' => '- select Experience Instance - '];
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }

    public function onRender() {
        //try{

            $this->roots = new Roots();
            $standards = $this->roots->getGradingStandards();
            $grading_scheme = $standards[0]->grading_scheme;

            $this->addCss("/plugins/delphinium/blossom/assets/css/jsgrid.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/storm.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/nouislider.min.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/gradebook.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/jquery-ui-slider-pips.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/jquery-ui.theme.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/jquery-ui.min.css");

            $this->addJs("/plugins/delphinium/blossom/assets/javascript/ui/jquery-ui.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/ui/jquery-ui-slider-pips.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/nouislider.min.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/gradebook_student.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/js/tab.js");
            $this->page['experienceInstanceId'] = $this->property('experienceInstance');
            $userRoles = $_SESSION['roles'];
            $this->page['userRoles'] = $userRoles;
            if (!is_null($this->property('experienceInstance'))) {
                $instance = ExperienceModel::find($this->property('experienceInstance'));
                $maxExperiencePts = $instance->total_points;

            }
            if (stristr($userRoles, 'Learner')) {
                if (!isset($_SESSION))
                {
                    session_start();
                }
                // $_SESSION['userID'] = 1230622;

                $bonusPenalties = $this->getBonusPenalties();

                $this->page['bonus'] = $bonusPenalties === 0 ? 0 : $bonusPenalties->bonus;
                $this->page['penalties'] = $bonusPenalties === 0 ? 0 : $bonusPenalties->penalties;

                $exp = new ExperienceComponent();
                $pts = $exp->getUserPoints();
                $this->page['totalPts'] = $pts;

                //get letter grade
                if (!is_null($this->property('experienceInstance'))) {
                    $instance = ExperienceModel::find($this->property('experienceInstance'));
                    $maxExperiencePts = $instance->total_points;

                    $grade = new GradeComponent();
                    $totalPoints = $pts + $bonusPenalties->bonus+$bonusPenalties->penalties;
                    $letterGrade = $grade->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);

                    $this->page['letterGrade'] = $letterGrade;
                }
                $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot_d3.js");
            } else if ((stristr($userRoles, 'Instructor')) || (stristr($userRoles, 'TeachingAssistant'))) {
                $this->getProfessorData();
                //$this->addCss("/plugins/delphinium/blossom/assets/css/light-js-table-sorter.css");
                $this->addCss("/plugins/delphinium/blossom/assets/css/jsgrid-theme.css");
                $this->addJs("/plugins/delphinium/blossom/assets/javascript/gradebook_professor.js");
                $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot_d3.js");
                $this->addJs("/plugins/delphinium/blossom/assets/javascript/jsgrid.min.js");

            }

            //modify grading scheme for display to users
            foreach($grading_scheme as $grade)
            {
                $grade->value = $grade->value * $maxExperiencePts;
            }
            $this->page['grading_scheme'] = json_encode($grading_scheme);

        // }
        // catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
        // {
        //     if($e->getCode()==401)//meaning there are two professors and one is trying to access the other professor's grades
        //     {
        //         return;
        //     }
        //     else
        //     {
        //         return \Response::make($this->controller->run('error'), 500);
        //     }
        // }
        // catch (\GuzzleHttp\Exception\ClientException $e) {
        //     return;
        // }
        // catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        // {
        //     if($e->getCode()==584)
        //     {
        //         return \Response::make($this->controller->run('nonlti'), 500);
        //     }
        // }
        // catch(\Exception $e)
        // {
        //     if($e->getMessage()=='Invalid LMS')
        //     {
        //         return \Response::make($this->controller->run('nonlti'), 500);
        //     }
        //     return \Response::make($this->controller->run('error'), 500);
        // }
    }

    function onGetContent() {
        return ['#modalContent' => 'This content will be pushed to the modalContent element'];
    }

    public function getStudentData($freshData = false, $studentId = null) {
        if ($this->roots === null) {
            $this->roots = new Roots();
        }
        //GET ANALYTICS STUDENT DATA
        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false, $studentId);
        //GET CLASS-WIDE ANALYTICS DATA
        $generalAnalytics = $this->roots->getAnalyticsAssignmentData(false);
        //GET ASSIGNMENT GROUPS
        $req = new AssignmentGroupsRequest(ActionType::GET, true, null, $freshData);
        $assignmentGroups = $this->roots->assignmentGroups($req);     //returns an eloquent collection
        $result = array();
        //Create a single array with the data we need
        foreach ($assignmentGroups as $group) {
            $wrap = new \stdClass();
            $wrap->group_name = $group->name;
            $wrap->content = array();
            foreach ($group->assignments as $assignment) {//loop through each assignment in the group
                //retrieve the corresponding assignment in $analytics ($group->assignment_id)
                $analyticsArr = $this->findAssignmentById(intval($assignment->assignment_id), $analytics, $generalAnalytics);

                if (count($analyticsArr) > 0) {
                    $analyticsObj = $analyticsArr[0]; //just take the first one. There shouldn't be more than one anyway
                    $obj = new \stdClass();
                    $obj->name = $assignment->name;
                    $obj->html_url = $assignment->html_url;
                    $obj->points_possible = $assignment->points_possible;
                    $obj->score = (isset($analyticsObj->submission)) ? ($analyticsObj->submission->score) : null;
                    $obj->max_score = (isset($analyticsObj->max_score)) ? ($analyticsObj->max_score) : null;
                    $obj->min_score = (isset($analyticsObj->min_score)) ? ($analyticsObj->min_score) : null;
                    $obj->first_quartile = (isset($analyticsObj->first_quartile)) ? ($analyticsObj->first_quartile) : null;
                    $obj->median = (isset($analyticsObj->median)) ? ($analyticsObj->median) : null;
                    $obj->third_quartile = (isset($analyticsObj->third_quartile)) ? ($analyticsObj->third_quartile) : null;

                    array_push($wrap->content, $obj);
                } else {
                    continue;
                }
            }

            $result[] = $wrap;
        }
        return $result;
    }

    private function getProfessorData() {

        $aggregateSubmissionScores = $this->aggregateSubmissionScores();
        $users = $this->roots->getStudentsInCourse();
        $userMasterArr= array();
        $courseEnd = $this->roots->getCourse()->end_at;
        foreach($users as $userCourse)
        {
            $userMasterArr[] = $userCourse->user;
        }
        $this->page['users'] = json_encode($userMasterArr);
        $this->users = $userMasterArr;
        $this->page['submissions'] = json_encode($aggregateSubmissionScores);

        $this->page['courseDate'] = json_encode($courseEnd);

        //comment these two lines
        // $submissionData = $this->matchSubmissionsAndUsers($users, $aggregateSubmissionScores);
        // $this->studentData = $submissionData;
        // chart data

        $this->page['chartData'] = json_encode($this->getRedLineData());
        $this->page['endDate'] = json_encode($courseEnd);
        $experience = new ExperienceComponent();
        $this->page['today'] = $experience->getRedLinePoints($this->property('experienceInstance'));

    }

    private function getRedLineData() {
        if (!is_null($this->property('experienceInstance'))) {
            $instance = ExperienceModel::find($this->property('experienceInstance'));
            $milestones = $instance->milestones;
            $this->page['numMilestones'] = count($milestones);

            $utcTimeZone = new DateTimeZone('UTC');
            $stDate = $instance->start_date->setTimezone($utcTimeZone);
            $endDate = $instance->end_date->setTimezone($utcTimeZone);

            $expComponent = new ExperienceComponent();

            $ptsPerSecond = $expComponent->getPtsPerSecond($stDate, $endDate, $instance->total_points);
            $milestoneData = array();
            foreach ($milestones as $milestone) {
                $secsTranspired = ceil($milestone->points / $ptsPerSecond);
                $intervalSeconds = "PT" . $secsTranspired . "S";
                $sDate = clone($stDate);
                $dueDate = $sDate->add(new \DateInterval($intervalSeconds));

                $mile = new \stdClass();
                $mile->points = $milestone->points;
                $mile->date = $dueDate->format('c');

                $milestoneData[] = $mile;
            }
            //fill in the rest of the days
            $newArr = $this->fillInMissingDays($stDate, $milestoneData);
            //merge arrays and order by date
            $final = array_merge($newArr, $milestoneData);
		
            usort($final, function($a, $b) {
                if ($a->date == $b->date && $a->points == $b->points) {
                    return 0;
                }
                if($a->date == $b->date && $a->points < $b->points){
                    return -1;
                }
                if($a->date == $b->date && $a->points > $b->points){
                    return 1;
                }
                return $a->date > $b->date ? 1 : -1;
            });

            return $final;
        } else {
            return array();
        }
    }

    public function getStudentChartData($studentId = null) {

        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }

        if (is_null($studentId)) {//get all student's data
            $masterArr = array();
            $users = $this->roots->getUsersInCourse();

            foreach ($users as $user) {
                try {
                    $userAnalytics = $this->roots->getAnalyticsStudentAssignmentData(false, $user->id);

                    //order submissions by date
                    usort($userAnalytics, function($a, $b) {

                        if (isset($a->submission) && ((!is_null($a->submission->submitted_at))) && (isset($b->submission)) && ((!is_null($b->submission->submitted_at)))) {
                            $adate = new DateTime($a->submission->submitted_at);
                            $bdate = new DateTime($b->submission->submitted_at);
                            $ad = $adate->getTimestamp();
                            $bd = $bdate->getTimestamp();
                            if ($ad == $bd) {
                                return 0;
                            }
                            return $ad > $bd ? 1 : -1;
                        } else {
                            return 0;
                        }
                    });


                    $userArr = $this->processAnalyticsChartData($userAnalytics);

                    $studentItem = new \stdClass();
                    $studentItem->id = $user->id;
                    $studentItem->analytics = $userArr;

                    $masterArr[] = $studentItem;
                } catch (\GuzzleHttp\Exception\ClientException $e) {
                    continue;
                }
            }


            return $masterArr;
        } else {//get this single student's data
            $userAnalytics = $this->roots->getAnalyticsStudentAssignmentData(false, $studentId);
            return $this->processAnalyticsChartData($userAnalytics);
        }
    }

    public function fillInMissingDays(DateTime $experienceStartDateUTC, $milestoneData) {

        //this is necessary if there's a gap between the startdate and the date the first milestone is due
        $firstD = DateTime::createFromFormat(DateTime::ISO8601, $milestoneData[0]->date);
        $newArr = array();
        $firstMilestoneDate = $firstD = DateTime::createFromFormat(DateTime::ISO8601, $milestoneData[0]->date);
        $i= date_diff($experienceStartDateUTC, $firstMilestoneDate);
        $d = intval($i->format('%R%a'));

        $zeroMile = new \stdClass();
        $zeroMile->points =0;
        $zeroMile->date = $experienceStartDateUTC->format('c');
        $newArr[] = $zeroMile;

        $interval = "P0D";
        $dayBeforeMile = new \stdClass();
        $dayBeforeMile->points =0;
        $dayB = $firstD->sub(new DateInterval($interval));
        $dayBeforeMile->date = $dayB->format('c');
        $newArr[] = $dayBeforeMile;


        for ($i = 0; $i <= count($milestoneData) - 1; $i++) {
            if ($i < count($milestoneData) - 1) {

                $firstD = DateTime::createFromFormat(DateTime::ISO8601, $milestoneData[$i]->date);
                $secondD = DateTime::createFromFormat(DateTime::ISO8601, $milestoneData[$i + 1]->date);

                $interval = date_diff($firstD, $secondD);
                $diff = intval($interval->format('%R%a'));
                for ($j = 0; $j <= $diff - 1; $j++) {
                    $mile = new \stdClass();
                    $mile->points = $milestoneData[$i]->points;

                    $newDate = $firstD->add(new DateInterval('P1D'));

                    $mile->date = ($j==$diff-1) ? $secondD->format('c') : $mile->date = $newDate->format('c');
                    $newArr[] = $mile;
                }
            }
        }//for
        return $newArr;
    }

    private function processAnalyticsChartData($analyticsData) {

        $carryingScore = 0;
        $masterArr = array();
        foreach ($analyticsData as $item) {
            if (isset($item->submission) && (!is_null($item->submission->submitted_at))) {
                if ($carryingScore === 0) {
                    $points = $item->submission->score;
                } else {
                    $points = $carryingScore;
                }

                $carryingScore = $carryingScore + floatval($item->submission->score);
                $dateObj = DateTime::createFromFormat(DateTime::ISO8601, $item->submission->submitted_at);

                $chartItem = new \stdClass();
                $chartItem->points = $points;
                $chartItem->date = $dateObj->format('c');

                $masterArr[] = $chartItem;
            } else {
                continue;
            }
        }
        return $masterArr;
    }

    /*private function getCourseDate() {
        
        session_start();
        if(!empty($_SESSION['courseID']) && !empty($_SESSION['userToken'])) {

        }
    }*/

    public function aggregateSubmissionScores() {

        $req = new SubmissionsRequest(ActionType::GET, array(), true, array(), true, true, true, false, true);

        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $result = $this->roots->submissions($req);
// echo json_encode($result);

        // $res = $this->orderSubmissionsByDate($result);
        $res = $this->orderSubmissionsByUsersAndDate($result);
        //set the results as a class variable
        $this->submissions = $res;

        //aggregate the scores
        $masterArr = array();
        $scoresArr = array();
        $subm = new \stdClass();
        $carryingScore = 0;
        $userId = 0;

        $student = new \stdClass();
        $studentItems = array();
        foreach ($res as $submission) {
            if (!isset($submission['submitted_at'])) {//skip items that have no submission date
                continue;
            }

            if ($userId === 0) {//init variables

                $student->id = $submission['user_id'];
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
            }
            $carryingScore = $carryingScore + $submission['score'];
            $item = new \stdClass();

            $item->points = $userId === 0 ? $submission['score'] : $carryingScore;
            $item->date = $submission['submitted_at'];

            if ($userId === 0 || $userId === $submission['user_id']) {//first loop or looping through same user

                //Add the current item to this user's item array
                $studentItems[] = $item;
                $userId = $submission['user_id'];
                $subm->score = $carryingScore;
            } else {//we moved on to a new student

                $student->items = $studentItems;
                //add the previous student to the master array
                $masterArr[] = $student;
                $student = new \stdClass(); //reset the student
                $student->id = $submission['user_id'];
                //if we have moved to a new student we must reset the carrying score
                $item->points = $submission['score'];
                $studentItems = array(); //reset the items array
                $studentItems[] = $item;
                //add the last item to the array
//                $subm->score = $carryingScore;
                $scoresArr[] = $subm;

                //and start a new one
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
                $carryingScore = $submission['score'];
                $userId = $submission['user_id'];
            }
        }

        //add the last student to the master array;
        if(count($res)>0)
        {
            $student->id = $userId;
            $student->items = $studentItems;
            $masterArr[] = $student;
            $scoresArr[] = $subm;
        }
        $this->page['submissions'] = json_encode($masterArr);
        // return $scoresArr;
        return $masterArr;
    }

    public function getAllUserClearedMilestoneData($experienceInstanceId)
    {
        //init experience variables
        $experienceInstance = ExperienceModel::find($experienceInstanceId);
        $maxExperiencePts = $experienceInstance->total_points;

        $utcTimeZone = new DateTimeZone('UTC');
        $stDate = $experienceInstance->start_date->setTimezone($utcTimeZone);
        $endDate = $experienceInstance->end_date->setTimezone($utcTimeZone);
        $expComponent = new ExperienceComponent();
        $ptsPerSecond = $expComponent->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
        $bonusPerSecond = $experienceInstance->bonus_per_day / 24 / 60 / 60;
        $bonusSeconds = $experienceInstance->bonus_days * 24 * 60 * 60;
        $penaltyPerSecond = $experienceInstance->penalty_per_day / 24 / 60 / 60;
        $penaltySeconds = $experienceInstance->penalty_days * 24 * 60 * 60;

        //get milestones
        $expComponent = new ExperienceComponent();
        $milestonesOrderedByPointsDesc = $expComponent->getMilestonesOrderedByPointsDesc($experienceInstanceId);

        //get grading standards
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $standards = $this->roots->getGradingStandards();
        $grading_scheme = $standards[0]->grading_scheme;

        //get all students in course
        $users = $this->roots->getStudentsInCourse();
        $userMasterArr= array();


        foreach($users as $userCourse)
        {
            $userObj = $this->getUserMilestoneInfo($userCourse->user, $milestonesOrderedByPointsDesc, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
                $penaltyPerSecond, $penaltySeconds, $maxExperiencePts, $grading_scheme);
            $userMasterArr[] = $userObj;
        }

        return $userMasterArr;

    }

    private function getPotential($experienceInstanceId, $userId)
    {
        $potential = new \stdClass();
        $potentialBonus = 0.0;
        $potentialPenalties = 0.0;
        $experienceComp = new ExperienceComponent();
        $milestoneClearanceInfo = $experienceComp->getMilestoneClearanceInfo($experienceInstanceId, $userId);
        foreach($milestoneClearanceInfo as $mileInfo)
        {
            if(!$mileInfo->cleared)
            {
                if($mileInfo->bonusPenalty>=0)
                {
                    $potentialBonus+=$mileInfo->bonusPenalty;
                }
                else{
                    $potentialPenalties+=$mileInfo->bonusPenalty;
                }
            }
        }
        $potential->bonus = $potentialBonus;
        $potential->penalties = $potentialPenalties;
        return $potential;

    }

    public function getSetOfUsersMilestoneInfo($experienceInstanceId, $userIds)
    {//init experience variables
        $experienceInstance = ExperienceModel::find($experienceInstanceId);
        $maxExperiencePts = $experienceInstance->total_points;

        $utcTimeZone = new DateTimeZone('UTC');
        $stDate = $experienceInstance->start_date->setTimezone($utcTimeZone);
        $endDate = $experienceInstance->end_date->setTimezone($utcTimeZone);
        $expComponent = new ExperienceComponent();
        $ptsPerSecond = $expComponent->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
        $bonusPerSecond = $experienceInstance->bonus_per_day / 24 / 60 / 60;
        $bonusSeconds = $experienceInstance->bonus_days * 24 * 60 * 60;
        $penaltyPerSecond = $experienceInstance->penalty_per_day / 24 / 60 / 60;
        $penaltySeconds = $experienceInstance->penalty_days * 24 * 60 * 60;

        //get milestones
        $expComponent = new ExperienceComponent();
        $milestonesOrderedByPointsDesc = $expComponent->getMilestonesOrderedByPointsDesc($experienceInstanceId);

        //get grading standards
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $standards = $this->roots->getGradingStandards();
        $grading_scheme = $standards[0]->grading_scheme;

        //get all students in course

        $roots = new Roots();
        $returned_data = $roots->getStudentsInCourseGradebook();
        $users = $returned_data['studentsInCourse'];
        $sections = $returned_data['studentsWithSection'];

        $filteredUsers = array();
        foreach($userIds as $userId)
        {
            $res = array_values(array_filter($users, function($elem) use($userId) {
                return intval($elem['user']['user_id']) === intval($userId);
            }));
            $filteredUsers = array_merge($filteredUsers,$res);
        }
        $masterArr=array();
        $potentisal_array = array();
        foreach($filteredUsers as $user)
        {
            $potentialOfUser = $this->getPotential($experienceInstanceId, $user->user_id);
            $item = $this->getUserMilestoneInfo($user, $milestonesOrderedByPointsDesc, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
                $penaltyPerSecond, $penaltySeconds, $maxExperiencePts, $grading_scheme, $sections, $potentialOfUser);
            $masterArr[] = $item;
        }
        return $masterArr;

    }

    private function getUserMilestoneInfo($user,$milestonesOrderedByPointsDesc, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
                                          $penaltyPerSecond, $penaltySeconds, $maxExperiencePts, $grading_scheme, $sections, $potentialOfUser)
    {

        //get User submissions
        $studentIds = array($user['user_id']);
        $assignmentIds = array();
        $multipleStudents = false;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = true;
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents,
            $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $userSubmissions = $this->roots->submissions($req);
        //sort submissions by date
        usort($userSubmissions, function($a, $b) {
            $ad = new DateTime($a['submitted_at']);
            $bd = new DateTime($b['submitted_at']);

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });


        //calculate raw experience points
        $carryingScore = 0.0;
        $totalPoints = 0.0;
        foreach($userSubmissions as $submission)
        {
            $carryingScore = $carryingScore+floatval($submission['score']);
        }
// echo "starting cleared milestones at ". json_encode(new \DateTime('now'))."--";
        //get milestone clearance info
        $expComponent = new ExperienceComponent();
        $userMilestoneInfo =  $expComponent->userClearedMilestones($milestonesOrderedByPointsDesc, $userSubmissions, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
            $penaltyPerSecond, $penaltySeconds);

// echo "ended cleared milestones at ". json_encode(new \DateTime('now'))."--";

        // echo json_encode($userMilestoneInfo);return;
        $userObj = new \stdClass();
        $userObj->id = $user['user']['user_id'];
        $userObj->name = $user['user']['name'];
        $userObj->alias = $user['alias'];
        //add link to user profile
        if (!isset($_SESSION)) {
            session_start();
        }
        $domain = $_SESSION['domain'];
        $courseId = $_SESSION['courseID'];
        $userObj->profile_url = "https://{$domain}/courses/{$courseId}/users/{$user['user_id']}";

        //calculate total bonus and penalties
        $obj = new \stdClass();
        $obj->bonus = 0;
        $obj->penalties = 0;

        foreach ($userMilestoneInfo as $item) {
            if (($item->cleared)) {
                if ($item->bonusPenalty > 0) {
                    $obj->bonus = $obj->bonus + $item->bonusPenalty;
                } else {
                    $obj->penalties = $obj->penalties + $item->bonusPenalty;
                }
            }
        }
        $userObj->bonuses = $obj->bonus;
        $userObj->penalties = $obj->penalties;
        $userObj->totalBP = $obj->bonus+$obj->penalties;
        $userObj->score = $carryingScore;
        $totalPoints = $carryingScore + $obj->bonus + $obj->penalties;
        $userObj->total = $totalPoints;

        //get letter grade
        $grade = new GradeComponent();
        $userObj->grade = $grade->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);
        $userObj->probable_penalty = $potentialOfUser->penalties;
        $userObj->possible_bonus = $potentialOfUser->bonus;
        $userObj->sections = implode("<br>",$sections[$userObj->id]);
        return $userObj;
    }

    public function getSetOfUsersTotalScores($experienceInstanceId, $userIds)//used for leaderboard
    {//get all students in course
        $dbHelper = new DbHelper();
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        $users = $dbHelper->getUsersInCourseWithRole($courseId, 'Learner');

        $filteredUsers = array();
        foreach($userIds as $userId)
        {
            $res = array_values(array_filter($users->toArray(), function($elem) use($userId) {
                return intval($elem['user']['user_id']) === intval($userId);
            }));
            $filteredUsers = array_merge($filteredUsers,$res);
        }

        if($experienceInstanceId>0)
        {
            //init experience variables
            $experienceInstance = ExperienceModel::find($experienceInstanceId);
            $maxExperiencePts = $experienceInstance->total_points;

            $utcTimeZone = new DateTimeZone('UTC');
            $stDate = $experienceInstance->start_date->setTimezone($utcTimeZone);
            $endDate = $experienceInstance->end_date->setTimezone($utcTimeZone);
            $expComponent = new ExperienceComponent();
            $ptsPerSecond = $expComponent->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
            $bonusPerSecond = $experienceInstance->bonus_per_day / 24 / 60 / 60;
            $bonusSeconds = $experienceInstance->bonus_days * 24 * 60 * 60;
            $penaltyPerSecond = $experienceInstance->penalty_per_day / 24 / 60 / 60;
            $penaltySeconds = $experienceInstance->penalty_days * 24 * 60 * 60;

            //get milestones
            $expComponent = new ExperienceComponent();
            $milestonesOrderedByPointsDesc = $expComponent->getMilestonesOrderedByPointsDesc($experienceInstanceId);

            //get grading standards
            if (is_null($this->roots)) {
                $this->roots = new Roots();
            }
            $standards = $this->roots->getGradingStandards();
            $grading_scheme = $standards[0]->grading_scheme;

            $masterArr=array();
            foreach($filteredUsers as $user)
            {
                $item = $this->getUserTotalScore($user, $milestonesOrderedByPointsDesc, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
                    $penaltyPerSecond, $penaltySeconds);
                $masterArr[] = $item;
            }
            return $masterArr;
        }
        else
        {//leaderboard is not using experience. Return only raw scores and aliases
            $masterArr=array();
            foreach($filteredUsers as $user)
            {
                $item = $this->getUserRawScores($user);
                $masterArr[] = $item;
            }
            return $masterArr;
        }
    }

    private function getUserTotalScore($user,$milestonesOrderedByPointsDesc, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
                                       $penaltyPerSecond, $penaltySeconds)
    {
        //get User submissions
        $studentIds = array($user['user_id']);
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, false,
            array(), true, false, true);
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $userSubmissions = $this->roots->submissions($req);
        //sort submissions by date
        usort($userSubmissions, function($a, $b) {
            if ((!is_null($a['submitted_at']))||(isset($a['submitted_at'])) && (!is_null($a['submitted_at'])) &&(isset($b['submitted_at']))) {
                $adate = new DateTime($a['submitted_at']);
                $bdate = new DateTime($b['submitted_at']);
                $ad = $adate->getTimestamp();
                $bd = $bdate->getTimestamp();
                if ($ad == $bd) {
                    return 0;
                }
                return $ad > $bd ? 1 : -1;
            } else {
                return 0;
            }
        });

        //calculate raw experience points
        $carryingScore = 0.0;
        $totalPoints = 0.0;
        foreach($userSubmissions as $submission)
        {
            $carryingScore = $carryingScore+floatval($submission['score']);
        }
        //get milestone clearance info
        $expComponent = new ExperienceComponent();
        $userMilestoneInfo =  $expComponent->userClearedMilestones($milestonesOrderedByPointsDesc, $userSubmissions, $ptsPerSecond, $stDate, $endDate, $bonusPerSecond, $bonusSeconds,
            $penaltyPerSecond, $penaltySeconds);
        $userObj = new \stdClass();
        $userObj->alias = $user['alias'];
        //add link to user profile

        //calculate total bonus and penalties
        $obj = new \stdClass();
        $obj->bonus = 0;
        $obj->penalties = 0;

        foreach ($userMilestoneInfo as $item) {
            if (($item->cleared)) {
                if ($item->bonusPenalty > 0) {
                    $obj->bonus = $obj->bonus + $item->bonusPenalty;
                } else {
                    $obj->penalties = $obj->penalties + $item->bonusPenalty;
                }
            }
        }
        $totalPoints = $carryingScore + $obj->bonus + $obj->penalties;
        $userObj->score = $totalPoints;
        $userObj->place = 0;
        return $userObj;
    }

    public function getUserRawScores($user)
    {
        //get User submissions
        $studentIds = array($user['user_id']);
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, false,
            array(), true, false, true);
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $userSubmissions = $this->roots->submissions($req);
        //calculate raw points
        $carryingScore = 0.0;
        foreach($userSubmissions as $submission)
        {
            $carryingScore = $carryingScore+floatval($submission['score']);
        }
        $userObj = new \stdClass();
        $userObj->alias = $user['alias'];
        $userObj->score = $carryingScore;
        $userObj->place = 0;
        return $userObj;
    }



    private function orderSubmissionsByUsersAndDate($arr) {
        $sorted = $this->array_orderby($arr, 'user_id', SORT_ASC, 'submitted_at', SORT_ASC);

        return $sorted;
    }

    function array_orderby($arr, $paramOne, $sortOne, $paramTwo, $sortTwo) {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    function comp($submissionA, $submissionB) {
        if ($submissionA['user_id'] == $submissionB['user_id']) {
            return $submissionA['submitted_at']->getTimestamp() - $submissionB['submitted_at']->getTimestamp();
        }
        return ($submissionA['user_id'] - $submissionB['user_id']);
    }

    public function matchSubmissionsAndUsers($users, $scores) {

        $allStudents = array();
        $standards = $this->roots->getGradingStandards();
        $grading_scheme = $standards[0]->grading_scheme;
        //get experience total points
        $experienceInstance = ExperienceModel::find($this->property('experienceInstance'));
        $maxExperiencePts = $experienceInstance->total_points;

        $utcTimeZone = new DateTimeZone('UTC');
        $stDate = $experienceInstance->start_date->setTimezone($utcTimeZone);
        $endDate = $experienceInstance->end_date->setTimezone($utcTimeZone);
        $expComponent = new ExperienceComponent();
        $ptsPerSecond = $expComponent->getPtsPerSecond($stDate, $endDate, $experienceInstance->total_points);
        $bonusPerSecond = $experienceInstance->bonus_per_day / 24 / 60 / 60;
        $bonusSeconds = $experienceInstance->bonus_days * 24 * 60 * 60;
        $penaltyPerSecond = $experienceInstance->penalty_per_day / 24 / 60 / 60;
        $penaltySeconds = $experienceInstance->penalty_days * 24 * 60 * 60;

        foreach ($users as $user) {
            $submissionsArr = $this->findScoreByUserId($user->user_id, $scores);

            //this will weed out any TA's and other people in the course who aren't necessarily students
            try {

                $userSubmissions = $expComponent->getSubmissions($user->user_id);

                $bonusPenalties = $this->getBonusPenaltiesNew($this->property('experienceInstance'), $userSubmissions, $ptsPerSecond, $stDate, $bonusPerSecond,
                    $bonusSeconds, $penaltyPerSecond, $penaltySeconds);

                // $bonusPenalties = $this->getBonusPenalties($user->user_id);
            } catch (\GuzzleHttp\Exception\ClientException $e) {

                continue;
            }
            $bonus = $bonusPenalties->bonus;
            $penalty = $bonusPenalties->penalties;
            $totalPoints = 0;

            $userObj = new \stdClass();
            $userObj->name = $user->user->name;
            $userObj->id = $user->user_id;
            //add link to user profile

            if (!isset($_SESSION)) {
                session_start();
            }
            $domain = $_SESSION['domain'];
            $courseId = $_SESSION['courseID'];
            $userObj->profile_url = "https://{$domain}/courses/{$courseId}/users/$user->id";
            $userObj->bonuses = $bonus;
            $userObj->penalties = $penalty;
            $userObj->totalBP = $bonus-$penalty;



            if (count($submissionsArr) >= 1) {
                $score = $submissionsArr[0];
                if(isset($score->score))
                {
                    $userObj->score = $score->score;
                    $totalPoints = $score->score + $bonus + $penalty;
                    $userObj->total = $totalPoints;
                }
                else
                {
                    $userObj->score = 0;
                    $totalPoints = $bonus + $penalty;
                    $userObj->total = $totalPoints;
                }
            } else {//no scores found for user
                $userObj->score = 0;
                $totalPoints = $bonus + $penalty;
                $userObj->total = $totalPoints;
            }

            //get letter grade
            $grade = new GradeComponent();
            $userObj->grade = $grade->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);
            $allStudents[] = $userObj;


        }
        return $allStudents;
    }

    private function findScoreByUserId($userId, $scoresComplex) {

        // $scores = $scoresComplex['bottom'];
        if(count($scores)>0)
        {
            $filteredItems = array_values(array_filter($scores, function($elem) use($userId) {
                return intval($elem->user_id) === intval($userId);
            }));
            return $filteredItems;
        }
        else
        {
            return [];
        }
    }

    private function getBonusPenalties($userId = null) {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($this->property('experienceInstance'))) && ($this->property('experienceInstance') > 0)) {
            return $experienceComp->calculateTotalBonusPenalties($this->property('experienceInstance'), $userId);
        } else {
            return 0;
        }
    }

    private function getBonusPenaltiesNew($experienceInstanceId, $userSubmissions, $ptsPerSecond, $stDateUTC, $bonusPerSecond, $bonusSeconds, $penaltyPerSecond, $penaltySeconds)
    {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($experienceInstanceId)) && ($experienceInstanceId > 0)) {
            return $experienceComp->calculateTotalBonusPenaltiesNew($experienceInstanceId, $userSubmissions, $ptsPerSecond, $stDateUTC, $bonusPerSecond, $bonusSeconds,
                $penaltyPerSecond, $penaltySeconds);
        } else {
            return 0;
        }
    }

    private function findAssignmentById($assignmentId, $analytics, $generalAnalytics) {

        $filteredItems = array();
        $filteredItems = array_values(array_filter($analytics, function($elem) use($assignmentId) {
            return $elem->assignment_id === $assignmentId;
        }));
        return $filteredItems;
    }

}

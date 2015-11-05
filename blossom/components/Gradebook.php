<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Utils;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use \DateTime;
use \DateTimeZone;
use \DateInterval;
use Carbon\Carbon;

class Gradebook extends ComponentBase {

    public $roots;
    public $studentData;
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

        $this->roots = new Roots();

        $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/gradebook.css");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");

        $this->page['userRoles'] = $_POST["roles"];
        if (stristr($_POST["roles"], 'Learner')) {
            $bonusPenalties = $this->getBonusPenalties();
            $this->page['bonus'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->bonus, 2);
            $this->page['penalties'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->penalties, 2);

            $exp = new ExperienceComponent();
            $pts = $exp->getUserPoints();
            $this->page['totalPts'] = $pts;
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot_d3.js");
        } else if ((stristr($_POST["roles"], 'Instructor')) || (stristr($_POST["roles"], 'TeachingAssistant'))) {
            $this->getProfessorData();
            $this->addCss("/plugins/delphinium/blossom/assets/css/light-js-table-sorter.css");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/gradebook_professor.js");
        }
    }

    function onGetContent() {
        return ['#modalContent' => 'This content will be pushed to the modalContent element'];
    }

    public function getStudentData($freshData = false) {
        if ($this->roots === null) {
            $this->roots = new Roots();
        }
        //GET ANALYTICS STUDENT DATA
        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false);
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

        $users = $this->roots->getUsersInCourse();

        $submissionData = $this->matchSubmissionsAndUsers($users, $aggregateSubmissionScores);

        $this->page['studentData'] = ($submissionData);
        $this->studentData = ($submissionData);
        //get chart data


        $this->page['chartData'] = json_encode($this->getRedLineData());
    }

    private function getRedLineData() {
        if (!is_null($this->property('experienceInstance'))) {
            $instance = ExperienceModel::find($this->property('experienceInstance'));

            $milestones = $instance->milestones;
			$this->page['numMilestones'] = count($milestones);
            //For this function we will keep the dates in local, because the operations are not timezone specific!
            //but for the most part we shoul convert to UTC and only display local on the client side.
            $stDate = $instance->start_date;
            $endDate = $instance->end_date;

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
            $newArr = $this->fillInMissingDays($milestoneData);
            //merge arrays and order by date
            $final = array_merge($newArr, $milestoneData);

            usort($final, function($a, $b) {
                if ($a->date == $b->date) {
                    return 0;
                }
                return $a->date > $b->date ? 1 : -1;
            });

            return $final;
        } else {
            return array();
        }
    }

    public function getStudentChartData($studentId = null) {

        echo $studentId;
        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }

        if (is_null($studentId)) {//get all student's data
            $masterArr = array();
            $users = $this->roots->getUsersInCourse();

//             $i = 0;
            foreach ($users as $user) {
                // if ($i === 10) {
//                     return $masterArr;
//                 }
//                 $i++;
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

    public function fillInMissingDays($milestoneData)
    {
        $newArr = array();
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

                    $mile->date = $newDate->format('c');
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

                $carryingScore = $carryingScore + intval($item->submission->score);
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

    public function aggregateSubmissionScores() {
        $req = new SubmissionsRequest(ActionType::GET, array(), true, array(), true, true, true, false, true);

        if (is_null($this->roots)) {
            $this->roots = new Roots();
        }
        $result = $this->roots->submissions($req);


        $res = $this->orderSubmissionsByUsersAndDate($result);

        //set the results as a class variable
        $this->submissions = $res;
        //aggregate the scores
        $masterArr = array();
        $scoresArr = array();
        $subm = new \stdClass();
        $carryingScore = 0;
        $userId = 0;

        $i = 0;
        
        $student = new \stdClass();
        $studentItems = array();
        foreach ($res as $submission) {
            if(!isset($submission['submitted_at']))//skip items that have no submission date
            {
               continue; 
            }
            if ($userId === 0) {//init variables
                $student->id = $submission['user_id'];
            }
            $item = new \stdClass();

            $item->points = $userId === 0 ? $submission['score'] : $carryingScore;
            $item->date = $submission['submitted_at'];
            
//             echo $submission['submitted_at'];
            $studentItems[] = $item;

            if ($userId === 0 || $userId === $submission['user_id']) {//first loop or looping through same user
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
                $carryingScore = $carryingScore + $submission['score'];
                $userId = $submission['user_id'];
            } else {//we moved on to a new student
           
//             echo json_encode($studentItems)."|||||||";
                $student->items = $studentItems;
                //add the previous student to the master array
                $masterArr[] = $student;
                $student = new \stdClass(); //reset the student
                $student->id = $submission['user_id'];
                $studentItems = array(); //reset the items array
                //add the last item to the array 
                $subm->score = $carryingScore;
                $scoresArr[] = $subm;

                //and start a new one
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
                $carryingScore = $submission['score'];
                $userId = $submission['user_id'];
            // if ($i === 15) {
//                 break;
//             }
//                 $i++;
            }


        }
// echo json_encode($masterArr);

        $this->page['submissions'] = json_encode($masterArr);
        //attach the last item we looped through
        $subm->score = $carryingScore;
        $scoresArr[] = $subm;
        return $scoresArr;
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

    private function matchSubmissionsAndUsers($users, $scores) {
        $allStudents = array();
        $standards = $this->roots->getGradingStandards();
        $grading_scheme = $standards[0]->grading_scheme;
        //get experience total points
        $instance = ExperienceModel::find($this->property('experienceInstance'));
        $maxExperiencePts = $instance->total_points;


        foreach ($users as $user) {
            $submissionsArr = $this->findScoreByUserId($user->id, $scores);

            //this will weed out any TA's and other people in the course who aren't necessarily students
            try {
                $bonusPenalties = $this->getBonusPenalties($user->id);
            } catch (\GuzzleHttp\Exception\ClientException $e) {

                continue;
            }

            $bonus = $bonusPenalties->bonus;
            $penalty = $bonusPenalties->penalties;
            $totalPoints = 0;

            $userObj = new \stdClass();
            $userObj->name = $user->name;
            $userObj->id = $user->sis_login_id;
            //add link to user profile

            if (!isset($_SESSION)) {
                session_start();
            }
            $domain = $_SESSION['domain'];
            $courseId = $_SESSION['courseID'];
            $userObj->profile_url = "https://{$domain}/courses/{$courseId}/users/$user->id"; //"https://uvu.instructure.com/courses/357790/users/1417818"
            $userObj->bonusPenalty = round($bonus + $penalty, 2);
            if (count($submissionsArr) >= 1) {
                $score = $submissionsArr[0];
                $userObj->score = round($score->score, 2);
                $totalPoints = $score->score + $bonus + $penalty;
                $userObj->total = round($totalPoints, 2);
            } else {//no scores found for user   	
                $userObj->score = 0;
                $totalPoints = $bonus + $penalty;
                $userObj->total = round($totalPoints, 2);
            }

            //get letter grade
            $userObj->grade = $this->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);
            $allStudents[] = $userObj;
        }

// echo json_encode($allStudents);
        return $allStudents;
    }

    private function getLetterGrade($studentPoints, $maxPoints, $gradingScheme) {
        if ($maxPoints === 0) {
            return "F";
        }
        $percentage = $studentPoints / $maxPoints;

        if ($percentage < 0) {
            return "F";
        }
        foreach ($gradingScheme as $grade) {
            if ($percentage >= $grade->value) {
                return $grade->name;
            }
        }
    }

    private function findScoreByUserId($userId, $scores) {
        $filteredItems = array_values(array_filter($scores, function($elem) use($userId) {
                    return $elem->user_id === $userId;
                }));
        return $filteredItems;
    }

    private function getBonusPenalties($userId = null) {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($this->property('experienceInstance'))) && ($this->property('experienceInstance') > 0)) {
            return $experienceComp->calculateTotalBonusPenalties($this->property('experienceInstance'), $userId);
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

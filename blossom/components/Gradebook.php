<?php

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Blossom\Models\Experience as ExperienceModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use \DateTime;

class Gradebook extends ComponentBase {

    public $roots;
    public $studentData;

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

    public function onRun() {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
    }

    public function onRender() {
        $this->roots = new Roots();

        $this->page['userRoles'] = $_POST["roles"];
        if (stristr($_POST["roles"], 'Learner')) {
            $result = $this->getStudentData();
            $this->page['data'] = json_encode($result);

            $bonusPenalties = $this->getBonusPenalties();
            $this->page['bonus'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->bonus, 2);
            $this->page['penalties'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->penalties, 2);
            $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
//             $this->addCss("/plugins/delphinium/blossom/assets/css/d3-tablesort.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/gradebook.css");
//             $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.tablesort.js");
        } else if ((stristr($_POST["roles"], 'Instructor')) || (stristr($_POST["roles"], 'TeachingAssistant'))) {
            $this->getProfessorData();
        }
    }

    private function getStudentData() {
        //GET ANALYTICS STUDENT DATA
        $analytics = $this->roots->getAnalyticsStudentAssignmentData(false);

        //GET ASSIGNMENT GROUPS
        $req = new AssignmentGroupsRequest(ActionType::GET, true, null, true);
        $assignmentGroups = $this->roots->assignmentGroups($req);     //returns an eloquent collection   

        $result = array();
        //Create a single array with the data we need
        foreach ($assignmentGroups as $group) {
            $wrap = new \stdClass();
            $wrap->group_name = $group->name;
            $wrap->content = array();
            foreach ($group->assignments as $assignment) {//loop through each assignment in the group
//                //retrieve the corresponding assignment in $analytics ($group->assignment_id)
                $analyticsArr = $this->findAssignmentById(intval($assignment->assignment_id), $analytics);

                if (count($analyticsArr) > 0) {
                    $analyticsObj = $analyticsArr[0]; //just take the first one. There shouldn't be more than one anyway

                    $obj = new \stdClass();
                    $obj->name = $assignment->name;
                    $obj->html_url = $assignment->html_url;
                    $obj->points_possible = $assignment->points_possible;
                    $obj->score = (isset($analyticsObj->submission)) ? ($analyticsObj->submission->score) : null;
                    $obj->max_score = $analyticsObj->max_score;
                    $obj->min_score = $analyticsObj->min_score;
                    $obj->first_quartile = $analyticsObj->first_quartile;
                    $obj->median = $analyticsObj->median;
                    $obj->third_quartile = $analyticsObj->third_quartile;

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
    }

    private function aggregateSubmissionScores() {
        $req = new SubmissionsRequest(ActionType::GET, array(), true, array(), true, true, true, false, true);
        $res = $this->roots->submissions($req);


        usort($res, function($a, $b) {
            $ad = $a['user_id'];
            $bd = $b['user_id'];

            if ($ad == $bd) {
                return 0;
            }

            return $ad > $bd ? 1 : -1;
        });

        //aggregate the scores
        $scoresArr = array();
        $subm = new \stdClass();
        $carryingScore = 0;
        $userId = 0;
        foreach ($res as $submission) {
            if ($userId === 0 || $userId === $submission['user_id']) {//first loop or looping through same user
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
                $carryingScore = $carryingScore + $submission['score'];
                $userId = $submission['user_id'];
            } else {//we moved on to a new student
                //add the last item to the array 
                $subm->score = $carryingScore;
                $scoresArr[] = $subm;

                //and start a new one
                $subm = new \stdClass();
                $subm->user_id = $submission['user_id'];
                $carryingScore = $submission['score'];
                $userId = $submission['user_id'];
            }
        }
        //attach the last item we looped through
        $subm->score = $carryingScore;
        $scoresArr[] = $subm;
        return $scoresArr;
    }

    private function matchSubmissionsAndUsers($users, $scores) {
        $allStudents = array();
        foreach ($users as $user) {
            $submissionsArr = $this->findScoreByUserId($user->id, $scores);

            $userObj = new \stdClass();
            $userObj->name = $user->name;
            $userObj->id = $user->sis_login_id;
            if (count($submissionsArr) >= 1) {
                $score = $submissionsArr[0];
                $userObj->score = $score->score;
            } else {//no scores found for user   	
                $userObj->score = 0;
            }
            $allStudents[] = $userObj;
        }


        return $allStudents;
    }

    private function findScoreByUserId($userId, $scores) {
        $filteredItems = array_values(array_filter($scores, function($elem) use($userId) {
                    return $elem->user_id === $userId;
                }));
        return $filteredItems;
    }

    private function getBonusPenalties() {
        $experienceComp = new ExperienceComponent();
        if ((!is_null($this->property('experienceInstance'))) && ($this->property('experienceInstance') > 0)) {
            return $experienceComp->calculateTotalBonusPenalties($this->property('experienceInstance'));
        } else {
            return 0;
        }
    }

    private function findAssignmentById($assignmentId, $analytics) {
        $filteredItems = array_values(array_filter($analytics, function($elem) use($assignmentId) {
                    return $elem->assignment_id === $assignmentId;
                }));
        return $filteredItems;
    }

}

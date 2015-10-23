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
    }
        
    public function onRender()
    {
        
        $this->roots = new Roots();

        $this->page['userRoles'] = $_POST["roles"];
        if (stristr($_POST["roles"], 'Learner')) {
            $bonusPenalties = $this->getBonusPenalties();
            $this->page['bonus'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->bonus, 2);
            $this->page['penalties'] = $bonusPenalties === 0 ? 0 : round($bonusPenalties->penalties, 2);

            $exp = new ExperienceComponent();
            $pts = $exp->getUserPoints();
            $this->page['totalPts'] = $pts;
        } else if ((stristr($_POST["roles"], 'Instructor')) || (stristr($_POST["roles"], 'TeachingAssistant'))) {
            $this->getProfessorData();
            $this->addCss("/plugins/delphinium/blossom/assets/css/light-js-table-sorter.css");
        }

        $this->addCss("/plugins/delphinium/blossom/assets/css/bootstrap.min.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/gradebook.css");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/boxplot_d3.js");
    }

	function onGetContent()
	{
//     	return "hi, modal";
		return ['#modalContent' => 'This content will be pushed to the modalContent element'];
	}
	
	
	// public function onRefreshData()
// 	{
// 	echo "here";
// 		$this->getStudentData(true);
// 	}
	
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
//                //retrieve the corresponding assignment in $analytics ($group->assignment_id)
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
		$standards = $this->roots->getGradingStandards();
		$grading_scheme =  $standards[0]->grading_scheme;
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
            $userObj->bonusPenalty = round($bonus + $penalty,2);
            if (count($submissionsArr) >= 1) {
                $score = $submissionsArr[0];
                $userObj->score = round($score->score,2);
                $totalPoints = $score->score+ $bonus + $penalty;
                $userObj->total = round($totalPoints,2);
            } else {//no scores found for user   	
                $userObj->score = 0;
                $totalPoints = $bonus + $penalty;
                $userObj->total = round($totalPoints,2);
            }
            
            //get letter grade
            $userObj->grade = $this->getLetterGrade($totalPoints, $maxExperiencePts, $grading_scheme);
            $allStudents[] = $userObj;
            
        }

// echo json_encode($allStudents);
        return $allStudents;
    }

	private function getLetterGrade($studentPoints, $maxPoints, $gradingScheme)
	{
		if($maxPoints===0)
		{
			return "F";
		}
		$percentage = $studentPoints/$maxPoints;
		
		if($percentage<0)
		{
			return "F";
		}
		foreach($gradingScheme as $grade)
		{
			if($percentage>=$grade->value)
			{
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

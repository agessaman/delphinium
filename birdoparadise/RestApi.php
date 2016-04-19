<?php namespace Delphinium\Birdoparadise;
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *....
 */
use Illuminate\Routing\Controller;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// student progress

class RestApi extends Controller 
{
    public function getAssignments()
	{
		//https://laravel.com/docs/5.2/controllers#basic-controllers
		// assignments
		$roots = new Roots();
		$req = new AssignmentsRequest(ActionType::GET);
		$res = $roots->assignments($req);

		//$assignmentIds = array();// for submissionsRequest
		$assignments = array();// for points_possible
		foreach ($res as $assignment) {
			//array_push($assignmentIds, $assignment["assignment_id"]);
			array_push($assignments, $assignment);
		}
		//$this->page['assignments']=json_encode($assignments);
		//echo json_encode($assignments);
		return $assignments;
	}
	public function getSubmissions()
	{
		// submissions
		$roots = new Roots();
		$req = new AssignmentsRequest(ActionType::GET);
		$res = $roots->assignments($req);

		$assignmentIds = array();// for submissionsRequest
		//$assignments = array();// for points_possible
		foreach ($res as $assignment) {
			array_push($assignmentIds, $assignment["assignment_id"]);
			//array_push($assignments, $assignment);
		}
		//$this->page['assignments']=json_encode($assignments);

		$studentIds = array($_SESSION['userID']);//['1604486'];//Test Student
		$allStudents = true;
		// $assignmentIds from above
		$allAssignments = true;
		$multipleStudents = true;
		$multipleAssignments = true;
		$includeTags = true;
		$grouped = true;

		$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments, $includeTags, $grouped);

		$submissions = $roots->submissions($req);
		//$this->page['submissions']=json_encode($submissions);// score
		//echo "Called";// success: data is ready
		//echo json_encode($submissions);
		return $submissions;
	}

}

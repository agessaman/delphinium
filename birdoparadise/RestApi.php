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

namespace Delphinium\Birdoparadise;

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

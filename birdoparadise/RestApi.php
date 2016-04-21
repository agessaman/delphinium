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
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// student progress

class RestApi extends Controller 
{
    //OBSOLETE//
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
		$assignments = array();// for points_possible
		foreach ($res as $assignment) {
			array_push($assignmentIds, $assignment["assignment_id"]);
			array_push($assignments, $assignment);
		}
		//$this->page['assignments']=json_encode($assignments);
		// STORE as global assignments
		
		$studentIds = array($_SESSION['userID']);//['1604486'];//Test Student
		$allStudents = false;
		// $assignmentIds from above
		$allAssignments = true;
		$multipleStudents = false;
		$multipleAssignments = true;
		$includeTags = true;//false not used
		$grouped = true;

		$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments, $includeTags, $grouped);

		$submissions = $roots->submissions($req);
		//$this->page['submissions']=json_encode($submissions);// score
		
		// STORE as global submissions
		return $submissions;
	}//OBSOLETE//
    
    
    
	public function calculateStars()
	{
		/* PSEUDO
            send module.id and return score, total
            
            get the module[modid] items
            get the assignments and submissions that match this module items
            
            calculate score & total possible
            return score & total
        */
        /*
            for each module item
            add up total from mod_item.content[0].points_possible;
            find the assignment that matches mod_item.title
            get its id to find matching submission.score
            add up score
            return score,total
        */
        $modid = \Input::get('modid');
        
		$roots = new Roots();
        
        // get the module matching id
        $moduleId = $modid;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = false;//true;
        $module = null;
        $moduleItem = null;
        $freshData = false;
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $module = $this->roots->modules($req);
        
        // all assignments
		$req = new AssignmentsRequest(ActionType::GET);
		$res = $roots->assignments($req);// all

		//$assignmentIds = array();// for submissionsRequest
		$assignments = array();// for matching id
		foreach ($res as $assignment) {
			//array_push($assignmentIds, $assignment["assignment_id"]);
			array_push($assignments, $assignment);//match mod.item.title to get assignment_id 
		}
        
        $student = $_SESSION['userID'];
        $total=0;
        $score=0;
        
        foreach ($module as $item) {
        // find the assignmentid that matches module item title
            //if has content
            if (array_key_exists('content', $item)) {
                //parse error" on line 143
                //$total = $total + intval($item.content[0].points_possible);
                $total = $total + intval($item.content.points_possible);
            
                $title = $item.title;
                if(in_array($title, $assignments)) {
                    $assignment = array_column($assignments, 'assignment_id');
                }
                
                $subScore = getSingleSubmissionSingleUserSingleAssignment($student,$assignment);
                $score = $score + $subScore;
            }
        }
        //parse error" on line 157
        return json_encode( {'score':$score,'total':$total,'modid':$modid} );
        
		/*
			do the getStars calculations here
			will also need the module, get it using id
		
	//ORIGINAL:function getStars(modid){
		// construct for modid = one module by id
		// get the module matching modid
        var mod1 = $.grep(modobjs, function(elem,index){ return elem.module_id == modid; });
        
        var total=0, score=0;
        var moditems = mod1.module_items;
		
		// for each module item add up score & total
        for(var i=0; i<moditems.length; i++) {

            // find a submission for moditem
            var title=moditems[i].title;
            var asgn1 = $.grep(assignments, function(elem,index){ return elem.name == title; });

            if(asgn1.length>0) {
                if(moditems[i].content.length>0){
                    total += moditems[i].content[0].points_possible;
                    var asgnid = asgn1[0].assignment_id;
                    //console.log(modid,'asgn1.assignment_id:',asgnid);
                    var subm1 = $.grep(subms, function(elem,index) {
                        return elem.assignment_id == asgnid;
                    });
                    //console.log('subm1:',subm1);
                    if(subm1.length>0) {
                        score += subm1[0].score;
                    }
                }
            }
        }
		
		
		*/
		//return $modid;
	}
    
    private function getSingleSubmissionSingleUserSingleAssignment($student,$assignment)
    {
        $studentIds = array($student);
        $assignmentIds = array($assignment);
        $multipleStudents = false;
        $multipleAssignments = false;
        $allStudents = false;
        $allAssignments = false;
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents,
            $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $this->roots->submissions($req);
        
        //echo json_encode($res);
        $score = intval($res.score);// ???
        return $score;
    }
}

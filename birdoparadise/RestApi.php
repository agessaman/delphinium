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
	public function getModuleStates()
    {
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = false;
        $includeContentItems = false;
        $module = null;
        $moduleItem = null;
        $freshData = true;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $roots = new Roots();
        $res = $roots->getModuleStates($req);
        return $res;
    }
	
	/*	get assignments & submission in modulemap if student
		send them here to calculate score,total
		return module id, score, total
	*/
	public function getModuleItemData()
	{
		// assignments to match module item title and find assignment_id
		$roots = new Roots();
		$req = new AssignmentsRequest(ActionType::GET);
		$res = $roots->assignments($req);
		
		$assignments = array();// title & id
		foreach ($res as $assignment) {
			array_push($assignments, $assignment);
		}
		//$this->page['assignments'] = json_encode($assignments);
		
		// submissions to calculate score & total
		if(!isset($_SESSION)) { session_start(); }
		$student = $_SESSION['userID'];
		$studentIds = array($student);//['1604486'];//Test Student
		$assignmentIds = array();
		$allStudents = false;
		$allAssignments = true;
		$multipleStudents = false;
		$multipleAssignments = true;
		
		$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
		$submitted = $roots->submissions($req);
		
		// get rid of any that are null or 0
		$valid = array();
		foreach ($submitted as $submission) {
			if($submission["score"] > 0) {
				array_push($valid, $submission);
			}
		}
		$submissions = $valid;
		
		//test:
		//$this->page['test'] = json_encode($valid);// no Err but how to retrieve it?
		//$this->page['submissions'] = json_encode($valid);
		// also get? $res = $roots->getModuleStates($req);
	
		//$assignments = json_decode($this->page['assignments']);// undefined property $page
		//$submissions = json_decode($this->page['submissions']);
		// modules
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
        $freshData = false;
		
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, $includeContentDetails, $module, $moduleItem, $freshData);
        $modules = $roots->modules($req);
		
		/*
            for each module id return id, score, total
            
            get the module[modid] items
            for each module item
                add to total using module_items.content[0].points_possible
                find the assignment that matches the module item title
                use the assignment id to get the matching submission score
                add to score
            construct & return array module.id, score, total
        */
        $total=0;
		$score=0;
		$asgnIds= array();
		foreach ($modules as $module) {
			$moditems = $module["module_items"]->toArray();
			$modid = $module["module_id"];
			$total=0;// reset for each module
			$score=0;
			$asgnIds= array();
			
			foreach ($moditems as $item) {
				$assignmentId=null;
				$title='';// reset for each item
				$subScore=0;
				// only module_items with points
				if(count($item["content"])>0) {
					$total = $total + intval($item["content"][0]["points_possible"]);
					
					$title = $item['title'];
					
					// find the assignment name that matches module item title 
					// get its id to find matching submission
					foreach($assignments as $key ) {
						if($title == $key["name"]) {
							$assignmentId = $key["assignment_id"];// id for submission
								array_push($asgnIds, $assignmentId);// test
							break;// done
						}
					}
					// find submission assignment_id that matches this assignment_id
					// get submission score
					foreach($submissions as $sub ) {
						if($assignmentId == $sub["assignment_id"]) {
							$subScore = $sub["score"];
							$score = $score + intval($subScore);
							break;// done
						}
					}
				}
			}
			
			$arr = array('modid'=>$modid,'score'=>$score,'total'=>$total,'asgnIds'=>$asgnIds);
			$modulescores[] = $arr;
		}
		//$this->page['modulescores']=json_encode($modulescores);
		//return ('assignments'=>$assignments, 'submissions'=>$submissions, 'modulescores'=>$modulescores);
		return ($modulescores);// already have assignments, submissions
	}
}

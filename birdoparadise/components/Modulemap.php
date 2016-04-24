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

namespace Delphinium\BirdoParadise\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// student progress
use Delphinium\Roots\Requestobjects\ModulesRequest;// for modulescores

class Modulemap extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'modulemap Component',
            'description' => 'Display Stem module data'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
		try
		{
            if (!isset($_SESSION)) { session_start(); }

            // comma delimited string
            $roleStr = $_SESSION['roles'];

            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other
            
            // code for both 
            $roots = new Roots();
            $moduledata = $roots->getModuleTree(false);
			$this->page['moduledata'] = json_encode($moduledata);
            
			// Learner getStars from modulescores
			if($roleStr == 'Learner') {
				$this->getModuleItemData();// assignments, submissions & modulescores
			}
			
            // ready to finish loading assets
            $this->addCss("/modules/system/assets/ui/storm.css");// loader spinner but changes Modal override css
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/font-autumn.css");
            $this->addCss("/plugins/delphinium/birdoparadise/assets/css/bop.css");
            $this->addJs("/plugins/delphinium/birdoparadise/assets/javascript/bop.js");
		
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
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
		//$this->assignments = $assignments;
		// STORED as global assignments
		$this->page['assignments'] = json_encode($assignments);
		
		// submissions to calculate score & total
		if(!isset($_SESSION)) { session_start(); }
		$student = $_SESSION['userID'];
		//if($student == null) { $student='1604486'; }
		$studentIds = array($student);//['1604486'];//Test Student
		$assignmentIds = array();
		$allStudents = false;
		$allAssignments = true;
		$multipleStudents = false;
		$multipleAssignments = true;
		
		$req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
		
		$submitted = $roots->submissions($req);
		
		// get rid of any that are null or 0
		$valid = array();// score > 0
		foreach ($submitted as $submission) {
			if($submission["score"] > 0) {
				array_push($valid, $submission);
			}
		}
		$submissions = $valid;// submissions
		// STORED as global submissions and in page
		$this->page['submissions'] = json_encode($valid);
		
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
            //for each module id STORE id, score, total
            
            get the module[modid] items
            for each module item
                add to total using module_items.content[0].points_possible
                find the assignment that matches the module item title
                use the assignment id to get the submission score
                add to score
            return score & total
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
				$title='';
				$subScore=0;// reset for each item
				// only items with points
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
					// get submission assignment_id that matches this assignment_id
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
		$this->page['modulescores']=json_encode($modulescores);
	}
	
}

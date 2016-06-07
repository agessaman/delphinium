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
use Delphinium\Blossom\Models\Competencies as CompetenceModel;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Requestobjects\ModulesRequest;//for locked & Instructor view
use Delphinium\Roots\Requestobjects\AssignmentsRequest;// for submissions
use Delphinium\Roots\Requestobjects\SubmissionsRequest;// score

class Competencies extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Competencies',
            'description' => 'Shows students completion of core Competencies'
        ];
    }
    
	public function defineProperties()
    {
        return [
            'instance'	=> [
                'title'             => 'Configuration:',
                'description'       => 'Optional',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	
	public function onRun()
    {
		try
        {
        /* Notes:
            is an instance set? yes show it
            else get all instances
                is there an instance with this course? yes use it
            else create dynamicInstance, save new instance, show it
        */
            if (!isset($_SESSION)) { session_start(); }
            $courseID = $_SESSION['courseID'];
            $name = $this->alias .'_'. $_SESSION['courseID'];
            
            // if instance has been set
            if( $this->property('instance') )
            {
                //instance set in CMS getInstanceOptions()
                $config = CompetenceModel::find($this->property('instance'));
                //add $course->id to $config for form field

            } else {
                // look for instances created for this course
				$instances = CompetenceModel::where('name','=', $name)->get();

				if(count($instances) === 0) { 
					// no record found so create a new dynamic instance
					$config = new CompetenceModel;// db record
					$config->Name = $name;// component_courseid
					$config->Size = 'Medium';//$config->size = '20%';
					$config->Color = '#4d7123';//uvu green
					$config->Animate = '1';//true
                    $config->course_id = $courseID;
					$config->save();// save the new record
				} else {
					//use the first record matching course
					$config = $instances[0];
				}
            }

            $this->page['competencyconfig'] = json_encode($config);
            // comma delimited string ?
            $roleStr = $_SESSION['roles'];

            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other

			/* get Assignments & Submissions ***** & enrolled students?
				submissions data is only available if viewed by a Learner
				Module Items data is used if Instructor

				if instructor, you can configure the component
				and view the tags set in Stem that define competencies
				todo: Instructor can choose a student to view their progress?
				todo: Instructor can configure Stem from here?
			*/
			
			$roots = new Roots();
            
            if($roleStr=='Instructor')
			{
				//https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
				$this->addCss("/plugins/delphinium/blossom/formwidgets/colorpicker/assets/vendor/colpick/css/colpick.css", "delphinium.blossom");
				$this->addJs("/plugins/delphinium/blossom/formwidgets/colorpicker/assets/vendor/colpick/js/colpick.js", "delphinium.blossom");
				$this->addCss("/plugins/delphinium/blossom/formwidgets/colorpicker/assets/css/colorpicker.css", "delphinium.blossom");
				$this->addJs("/plugins/delphinium/blossom/formwidgets/colorpicker/assets/js/colorpicker.js", "delphinium.blossom");


				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\Blossom\Controllers\Competencies();
				$formController->create('frontend');

				// Append the formController to the page
				$this->page['competencyform'] = $formController;
				// Use the primary key of the record you want to update
				$this->page['competencyrecordId'] = $config->id;
				// Append Instructions page
				$instructions = $formController->makePartial('competencyinstructions');
				$this->page['competencyinstructions'] = $instructions;
			}

			/* modules data used by both roles
				module items construct tags */
			$moduleId = null;
			$moduleItemId = null;
			$includeContentDetails = true;
			$includeContentItems = true;
			$module = null;
			$moduleItem = null;
			$freshData = false;

			$req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems,
				$includeContentDetails, $module, $moduleItem , $freshData);

			$moduleData = $roots->modules($req);

			/* simplify the module data */
			$modArr = $moduleData->toArray();
			$simpleModules = array();
			foreach($modArr as $item)
			{
				$mod = new \stdClass();
				$mod->id = $item['module_id'];
				$mod->title=$item['name'];
				$mod->locked=$item['locked'];// OBSOLETE
				$mod->items =$item['module_items'];
				$simpleModules[] = $mod;
			}
			
			if($roleStr=='Learner')
			{
				/* add module states [id,state] 
					only available if Learner */
				$moduleId = null;
				$moduleItemId = null;
				$includeContentDetails = false;
				$includeContentItems = false;
				$module = null;
				$moduleItem = null;
				$freshData = true;
						
				$req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, $includeContentDetails, $module, $moduleItem, $freshData);
				$states = $roots->getModuleStates($req);
				
				foreach($states as $item)
				{
					$item_id = $item->module_id;
					$item_state = $item->state;
					$counter=0;
					foreach($simpleModules as $mod)
					{
						if($mod->id == $item_id)
						{
							$simpleModules[$counter]->module_id = $item_id;//verify
							$simpleModules[$counter]->state = $item_state;
							break;
						}
						$counter = $counter +1;
					}
					
				}
			}
			/* used by both to construct tags */
			$this->page['competencymodules'] = json_encode($simpleModules);
            
            if($roleStr=='Learner')
			{
				$req = new AssignmentsRequest(ActionType::GET);
				$assignments = $roots->assignments($req);
				//$this->page['assignments']=json_encode($assignments);

                $studentIds = array($_SESSION['userID']);
                $allStudents = false;
				$assignmentIds = array();
                $allAssignments = true;
                $multipleStudents = false;
                $multipleAssignments = true;
                $includeTags = true;
                $grouped = true;

                $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments, $includeTags, $grouped);
				$submissions = $roots->submissions($req);

				/* match $simpleModules items to assignment by title
					use assignment_id to find matching submission
					 
					add points_possible to submissions
					add module_item_id to submissions
					add name & content_id to submissions
				*/

				foreach ($simpleModules as $module) {
					$moditems = $module->items;//module_items

					foreach ($moditems as $item) {
						$assignmentId=null;
						$title='';
						$counter=0;
						// only items with points
						if(count($item["content"])>0) {
							
							$title = $item['title'];
							
							/*	find the assignment name that matches module item title 
								get its id to find matching submission */
							foreach($assignments as $key ) {
								if($title == $key["name"]) {
									$assignmentId = $key["assignment_id"];// id for submission
									break;// done
								}
							}
							/* get submission assignment_id that matches this assignment_id */
							foreach($submissions as $sub ) {
								if($assignmentId == $sub["assignment_id"]) {
									/* add content id to submissions to find matching assignment 
										in js function displayDetails 
									*/
									$submissions[$counter]["assignment_id"] = $item["content"][0]["content_id"];
									// unused
									$submissions[$counter]["name"] = $title;
									$submissions[$counter]["points_possible"] = $item["content"][0]["points_possible"];
									$submissions[$counter]["module_item_id"] = $item["content"][0]["module_item_id"];
									
									break;// done
								}
								$counter=$counter+1;
							}
						}
					}
				}
				$this->page['competencysubmissions']=json_encode($submissions);// score
            }

			// ready to finish loading assets
			$this->addCss("/modules/system/assets/ui/storm.css", "core");// loader storm changes modal-header override css
			$this->addJs("/modules/system/assets/ui/storm-min.js", "core");
			$this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
			$this->addCss("/plugins/delphinium/blossom/assets/css/competencies.css");
			$this->addJs("/plugins/delphinium/blossom/assets/javascript/competencies.js");
       }
        catch (\GuzzleHttp\Exception\ClientException $e) {
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

    public function getInstanceOptions()
    {
		/* https://octobercms.com/docs/plugin/components#dropdown-properties
		*  The method should have a name in the following format: get*Property*Options()
		*  where Property is the property name
		*  Fill the Competencies Configuration [dropdown] in CMS
		*/
		$instances = CompetenceModel::all();
        $array_dropdown = ['0'=>'- Optional - '];//id, text in dropdown

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }
        return $array_dropdown;
    }
	
	/**
	* update, add course_id :  Need to remove this one???
	* save to database and return updated
	* id is disabled in fields.yaml
	* id & course are also hidden
	* $data gets .id from config setting hidden field
	* called from instructorView configure settings
	*/
	public function onUpdate()
    {
		$data = post('Competencies');
		$did = intval($data['id']);
		$config = CompetenceModel::find($did);
		$config->Name = $data['Name'];
		$config->Size = $data['Size'];
		$config->Color = $data['Color'];
		$config->Animate = $data['Animate'];
		$config->course_id = $data['course_id'];//hidden field
		$config->save();// update original record 
		return json_encode($config);
    }
    
}

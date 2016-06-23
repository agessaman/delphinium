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

namespace Delphinium\Vanilla\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Vanilla\Models\Vanilla as VanillaModel;

class Vanilla extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Vanilla Component',
            'description' => 'No description provided yet...'
        ];
    }
/* ORIGINAL CODE
    public function defineProperties()
    {
        return [
			'name'        => 'vanilla Component',
            'description' => 'No description provided yet...'
		];
    }
**** NEW CODE */
	/** Added for instance dropdown in CMS */
    public function defineProperties()
    {
        return [
            'instance'	=> [
                'title'             => '(Optional) Vanilla instance',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }
	/**Added
	*  https://octobercms.com/docs/plugin/components#dropdown-properties
	*  The method should have a name in the following format: get*Property*Options()
	*  where Property is the property name
	*  Fills the Configuration [dropdown] in CMS
	*/
    public function getInstanceOptions()
    {
		$instances = VanillaModel::all();// records
		if(count($instances) === 0) {
			return $array_dropdown = ['0' => "No instance available."];
		} else {
			$array_dropdown = ['0'=>'- select Instance - '];//id, text in dropdown
			// populate CMS dropdown
			foreach ($instances as $instance) {
				$array_dropdown[$instance->id] = $instance->Name;
			}
		}
        return $array_dropdown;
    }
	
/* ORIGINAL CODE
	public function onRun()
	{
		$config = VanillaModel::all();
		$this->page['config']=$config;
	}
**** NEW CODE */
	/** Added
	 *  Requires the Dev component in CMS page and minimal layout
	 */
    public function onRun()
    {
        try
        {
            /*Notes:
            -When configuring the component on a page we will provide the option of selecting a backend instance of the component. 
                if an instance has been selected, then we will just use that.
            -If no instance has been selected, then we will look in the DB for a component with the alias_courseId name. If found, we'll return it.
            -If not found, then we will create a new instance with the name alias_courseId.
            
			Requires minimal.htm layout
            Requires the Dev component set up from Here:
            https://github.com/ProjectDelphinium/delphinium/wiki/3.-Setting-up-a-Project-Delphinium-Dev-environment-on-localhost
            */
            if (!isset($_SESSION)) { session_start(); }
            $courseID = $_SESSION['courseID'];
			$name = $this->alias .'_'. $_SESSION['courseID'];
            
            // if instance has been set
            if( $this->property('instance') )
            {
                //use the instance set in CMS dropdown
                $config = VanillaModel::find($this->property('instance'));

            } else {
				// look for instances created for this course
				$instances = VanillaModel::where('name','=', $name)->get();
				
				if(count($instances) === 0) { 
					// no record found so create a new dynamic instance
					$config = new VanillaModel;// db record
					$config->name = $name;
					// add your fields
					//$config->size = '20%';
					$config->save();// save the new record
				} else {
					//use the first record matching course
					$config = $instances[0];
				}
            }
			// use the record in the component and frontend form 
            $this->page['config'] = json_encode($config);
            
			/** get roles, a comma delimited string
			 * check if Student
			 * if not then set to Instructor. disregard any other roles?
			 * role is used to determine functions and display options
			 */
            $roleStr = $_SESSION['roles'];
			
            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other
            
            // include your css. Note: bootstrap.min.css is part of minimal layout
            //$this->addCss("/plugins/delphinium/vanilla/assets/css/bootstrap.min.css");
			// javascript had to be added to default.htm to work correctly
            //$this->addJs("/plugins/delphinium/vanilla/assets/javascript/jquery.min.js");
            
            // include the backend form with instructions for instructor.htm
            if(stristr($roleStr, 'Instructor'))
			{
				/** Added: 3/30/16 
                    Flash message when form is updated from the instructor frontend
                    https://octobercms.com/docs/ui/flashmessage
                Note: storm-min.js?v316:2886 Uncaught Error: Trigger condition is not specified.
                */
                //$this->addCss('/modules/system/assets/ui/storm.css', 'core');
                //$this->addJs('/modules/system/assets/ui/storm-min.js', 'core');
                //$this->addCss('/modules/system/assets/ui/storm.less', 'core');
                
                //https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
				// Build a back-end form with the context of 'frontend'
				$formController = new \Delphinium\Vanilla\Controllers\Vanilla();
				$formController->create('frontend');
				
                // Use the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
				// Append the formController to the page
				$this->page['form'] = $formController;
                
                // Append the Instructions to the page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
                //code specific to instructor.htm goes here
            }
            
            if(stristr($roleStr, 'Learner'))
			{
				//code specific to the student.htm goes here
            }
			// code used by both goes here
			
        // Error handling requires nonlti.htm
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
	
//New function
    /**Added
	*  frontend update component submit button
	*  save to database and return updated record
    *
    *  id can be disabled in fields.yaml
    *  id & course can be hidden
    *  $data gets .id from config.id instructor.htm
    *  called from instructor.htm configure settings modal
	*/
	public function onUpdate()
    {
        $data = post('Vanilla');//component name
        $did = intval($data['id']);// convert string to integer
        $config = VanillaModel::find($did);// retrieve existing record
        $config->name = $data['name'];// change to new data
        //echo json_encode($config);//($data);// testing
        
		// add your fields to update
        $config->custom = $data['custom'];
		$config->save();// update original record 
		return json_encode($config);// back to instructor view
    }
    /* End of class */
}
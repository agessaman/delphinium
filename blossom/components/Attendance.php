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

use Delphinium\Blossom\Controllers\Attendance as MyController;
use Delphinium\Blossom\Controllers\AttendanceSessions;
use Delphinium\Blossom\Models\Attendance as MyModel;
use Delphinium\Blossom\Models\AttendanceSession as Sessions;
use Delphinium\Roots\Models\AssignmentGroup;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Roots;
use Cms\Classes\ComponentBase;
class Attendance extends ComponentBase
{
    public $activeSession = false;
    /**
     * @return array An array of details to be shown in the CMS section of OctoberCMS
     */
    public function componentDetails()
    {
        return array('name' => 'Attendance', 'description' => 'A component for creating attendance sessions (student and professor)');
    }
    /**
     * @return array Array of properties that can be configured in this instance of this component
     */
    public function defineProperties()
    {
        return array('instance' => array('title' => '(Optional) Attendance instance', 'description' => 'Select the attendance instance to display. If an instance is selected, it will be
                                    the configuration for all courses that use this page. Leaving this field blank will allow
                                    different configurations for every course.', 'type' => 'dropdown', 'default' => 0));
    }
    /**
     * @return array An array of instances (eloquent models) to populate the instance dropdown to configure this component
     */
    public function getInstanceOptions()
    {
        //In order for this to work, you must create a model and controller to store the instances of this component.
        //Modify use statement above to include the model and controller and uncomment the following code
        $instances = MyModel::all();
        if (count($instances) === 0) {
            return $array_dropdown = array('0' => 'No instances available.');
        } else {
            $array_dropdown = array('0' => '- select MyModel Instance - ');
            foreach ($instances as $instance) {
                $array_dropdown[$instance->id] = $instance->name;
            }
            return $array_dropdown;
        }
    }
    /**
     * This function will run every time this component runs. To use this component, drop it on a OctoberCMS page along with the dev component
     * (for development) or LTIConfiguration component (for production)
     */
    public function onRun()
    {

//        try {
            $config = $this->getInstance();
            //use the record in the component and frontend form
            $this->page['config'] = json_encode($config);
            //Use the primary key of the record you want to update
            $this->page['recordId'] = $config->id;
            if (!isset($_SESSION)) {
                session_start();
            }
            //get LMS roles --used to determine functions and display options
            $roleStr = $_SESSION['roles'];
            $this->page['role'] = $roleStr;
            //THIS NEXT SECTION WILL PROVIDE TEACHERS WITH FRONT-EDITING CAPABILITIES OF THE BACKEND INSTANCES.
            //A CONTROLLER AND MODEL MUST EXIST FOR THE INSTANCES OF THIS COMPONENT SO THE BACKEND FORM CAN BE USED IN THE FRONT END FOR THE TEACHERS TO USE
            //ALSO, AN INSTRUCTIONS PAGE WITH THE NAME instructor.htm MUST BE ADDED TO YOUR CONTROLLER DIRECTORY, AFTER THE CONTROLLER IS CREATED
            //IN Delphinium\Blossom\controllers\Attendance\_instructions.htm
            // include the backend form with instructions for instructor.htm

        $this->addCss('/modules/system/assets/ui/storm.css', 'core');
        $this->addCss('/modules/system/assets/ui/storm.less', 'core');
        $this->addJs('/modules/system/assets/ui/js/flashmessage.js', 'core');
        $this->addJs('/plugins/delphinium/blossom/assets/js/attendance_instructor.js');
            if (stristr($roleStr, 'Instructor') || stristr($roleStr, 'TeachingAssistant'))
            {

                $formController = new MyController();
                $formController->create('frontend');
                //Append the formController to the page
                $this->page['attendanceform'] = $formController;
                //Append the Instructions to the page
                $instructions = $formController->makePartial('attendanceinstructions');
                $this->page['attendanceinstructions'] = $instructions;


                $sessController = new AttendanceSessions();
                $sessController->create('frontend');
                //Append the formController to the page

                $this->page['attendancesession'] = $sessController;

            } else {
                if (stristr($roleStr, 'Learner')) {
                    //see if there's an active session, and if so, check to see whether the student has submitted the assignment
                    $session = $this->getActiveSession();
                    if(!$session)return;

                    if(!isset($_SESSION))
                    {
                        session_start();
                    }
                    $userId = $_SESSION['userID'];
                    $studentIds = array($userId);
                    $assignmentIds = array($session->assignment_id);
                    $multipleStudents = false;
                    $multipleAssignments = false;
                    $allStudents = false;
                    $allAssignments = false;

                    //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
                    $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents,
                        $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);

                    $roots = new Roots();
                    $res = $roots->submissions($req);
                    if(count($res)>0)
                    {
                        if($res&&(isset($res[0]['submitted_at'])||(isset($res[0]['graded_at']))))
                        {
                            $this->page->submitted = true;
                        }
                    }
                }
            }

        //set the code
        $session = $this->getActiveSession();
        if($session)
        {
            $this->page->code = $this->getActiveSession()->code;
        }
        else
        {
            $this->page->code = null;
        }

//        } catch (\GuzzleHttp\Exception\ClientException $e) {
//            return;
//        } catch (Delphinium\Roots\Exceptions\NonLtiException $e) {
//            if ($e->getCode() == 584) {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//        } catch (\Exception $e) {
//            if ($e->getMessage() == 'Invalid LMS') {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//            return \Response::make($this->controller->run('error'), 500);
//        }
    }


    /**
     * Retrieves instance of this component. If no specific instance was selected in the CMS configuration of this component
     * then it will create a dynamic instance based on the alias_courseId in which this component was launched
     * @param null $name The name of the component
     * @return mixed Instance of Component
     */
    private function getInstance($name = null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        //if instance has been set
        if ($this->property('instance')) {
            //use the instance set in CMS dropdown
            $config = MyModel::find($this->property('instance'));
        } else {
            if (is_null($name)) {
                $name = $this->alias . '_' . $courseId;
            }
            $config = MyModel::firstOrNew(array('name' => $name));
            if (is_null($config->name)) {
                $config->name = $name;
            }
            if (is_null($config->animate)) {
                $config->animate = 1;
            }
            if (is_null($config->size)) {
                $config->size = 100;
            }
        }
        $config->save();
        return $config;
    }
    /**
     * Ajax Handler for when teachers update the component from their view
     * @return string Json encoded instance of component
     */
    public function onUpdate()
    {
        $data = post('Attendance');
        //model name
        $id = $this->page['attendancerecordId'];
        // convert string to integer
        $config = $this->getInstance($data['name']);
        // retrieve existing record
        //update record with new data coming from POST
        $config->name = $data['name'];
        $config->animate = intval($data['animate']);
        $config->size = intval($data['size']);
        //TODO: must finish updating the rest of the fields in your table
        $config->save();
        // update original record
        return json_encode($config);
    }

    public function onCreateSession()
    {
        $roots = new Roots();
        $data = post('AttendanceSession');

        //create the assignment for this attendance
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        /**ASSIGNMENT GROUP**/
        $assignmentGrp = $this->getAssignmentGroup($courseId);

        /**ASSIGNMENT**/

        $assignment = new Assignment();
        $assignment->name = $data['title'];
        $assignment->points_possible = $data['points'];
        $assignment->assignment_group_id = $assignmentGrp->assignment_group_id;
        $assignment->course_id = $courseId;

        $date = new \DateTime('now');
        $intervalString = "PT{$data['duration_minutes']}M";
        $date->add(new \DateInterval($intervalString));

        $assignment->due_at = $date;

        $req = new AssignmentsRequest(ActionType::POST, null, null, $assignment);

//        $assignment = $roots->assignments($req);
//
//        echo json_encode($assignment);
        //create the session with the assignment id, code, etc.
        $session = new Sessions();
        $session->course_id = $courseId;
        $session->assignment_id = 2710395;//TODO replace this with the dynamically generated assignment id
        $session->title = $data['title'];
        $session->start_at = new \DateTime('now');
        $session->duration_minutes = $data['duration_minutes'];
        $session->code = $this->generateCode();
        $session->save();

//        $response = new \std
        return ['message'=>"Session successfully created",'success'=>1];
        //display the code
    }

    private function getAssignmentGroup($courseId)
    {
        $roots = new Roots();
        $assignmentGrp = AssignmentGroup::where(['course_id' => $courseId,
            'name' => 'Attendance',
        ])->first();
        if(!$assignmentGrp)
        {
            $assignmentGrp = new AssignmentGroup();
            $assignmentGrp->name = "Attendance";
            $assignmentGrp->course_id = $courseId;

            $include_assignments = false;
            $assignmentGpId = null;
            $fresh_data = false;
            $req = new AssignmentGroupsRequest(ActionType::POST, $include_assignments, $assignmentGpId, $fresh_data);

            $res = $roots->assignmentGroups($req, $assignmentGrp);
            return $res;
        }
        return $assignmentGrp;
    }

    private function generateCode()
    {
        $rootUrl= base_path();//(\Config::get('app.url', 'backend'));
        $fullUrl = $str = str_replace('/', '\\', $rootUrl."/plugins/delphinium/blossom/assets/js/attendance_words.csv");
        $row = 1;
        $codes=[];
        if (($handle = fopen($fullUrl, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    $codes[]= $data[$c];
                }
            }
            fclose($handle);
        }
        $number = rand(1, count($codes)-1);
        $codeFinal =$codes[$number];
        return $codeFinal;
    }

    public function onRecordAttendance()
    {
        $data = post('AttendanceSession');
        $providedCode = $data['code'];
        $session = $this->getActiveSession();
        if($providedCode!==$session->code)
        {
            return ['message'=>"The code you provided is wrong. Please try again",'success'=>0];
        }
        if(!isset($_SESSION))
        {
            session_start();
        }
        $userId = $_SESSION['userID'];
        $studentIds = array($userId);
        $assignmentIds = array(2710395);//TODO: replace this with the automatically generated assignment id
        $multipleStudents = false;
        $multipleAssignments = false;
        $allStudents = false;
        $allAssignments = false;

        $req = new SubmissionsRequest(ActionType::POST, $studentIds, $allStudents,
            $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);

        //parameters
        $params = array(
            "submission[submission_type]"=>"online_text_entry",
            "submission[body]"=>"Present");

        $roots = new Roots();
//        $res = $roots->submissions($req, $params);
//        return $res;
        return ['message'=>"Your attendance has been recorded",'success'=>1];

    }

    public function getActiveSession()
    {
        //BOTH TEACHER AND STUDENT
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        //check to see if there's an active session.
        $lastSession = Sessions::where(array('course_id' => $courseId))->orderBy('start_at', 'DESC')->first();
        if (!$lastSession) {
            return null;
        } else {
            //check to see if the last session is still active
            $date = new \DateTime($lastSession->start_at);
            $intervalString = "PT{$lastSession->duration_minutes}M";
            $endDate = $date->add(new \DateInterval($intervalString));

            if ($endDate < new \DateTime('now')) {
                return null;
            } else {
                return $lastSession;
            }
        }
    }
}
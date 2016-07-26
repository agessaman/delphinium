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
namespace Delphinium\Orchid\Components;

use Delphinium\Orchid\Controllers\Attendance as MyController;
use Delphinium\Orchid\Controllers\AttendanceSessions;
use Delphinium\Orchid\Models\Attendance as MyModel;
use Delphinium\Orchid\Models\AttendanceSession as Sessions;
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
    public $attendancerecordId;
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
            $this->page->attendancerecordId = $config->id;//this will be used as a parameter in the instructor view to load the appropriate instance of this component
            if (!isset($_SESSION)) {
                session_start();
            }
            //get LMS roles --used to determine functions and display options
            $roleStr = $_SESSION['roles'];
            $this->page['role'] = $roleStr;
            //THIS NEXT SECTION WILL PROVIDE TEACHERS WITH FRONT-EDITING CAPABILITIES OF THE BACKEND INSTANCES.
            //A CONTROLLER AND MODEL MUST EXIST FOR THE INSTANCES OF THIS COMPONENT SO THE BACKEND FORM CAN BE USED IN THE FRONT END FOR THE TEACHERS TO USE
            //ALSO, AN INSTRUCTIONS PAGE WITH THE NAME instructor.htm MUST BE ADDED TO YOUR CONTROLLER DIRECTORY, AFTER THE CONTROLLER IS CREATED
            //IN Delphinium\Orchid\controllers\Attendance\_instructions.htm
            // include the backend form with instructions for instructor.htm

            //check if the compnent has custom css
            if($config&&$config->custom_css)
            {
                $cssStr = $config->custom_css;
                $this->page['custom_css'] = $cssStr;
            }
            $this->addCss('/modules/system/assets/ui/storm.css', 'core');
            $this->addCss('/modules/system/assets/ui/storm.less', 'core');
            $this->addJs('/modules/system/assets/ui/js/flashmessage.js', 'core');
            $this->addJs('/plugins/delphinium/orchid/assets/js/attendance_instructor.js');

            if (stristr($roleStr, 'Instructor') || stristr($roleStr, 'TeachingAssistant'))
            {
                $this->page['statsRecordId'] = $this->statsInstanceId;
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

//                    $this->addJs('/plugins/delphinium/orchid/assets/js/attendance_student.js');
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
            $assignment_id = $session->assignment_id;
            $req = new AssignmentsRequest(ActionType::GET, $assignment_id, false, null, false);

            $roots = new Roots();
            $assignment = $roots->assignments($req);
            $this->page->total_points = $assignment['points_possible'];
            $this->page->percentage_fifteen = $assignment['points_possible']*($session->percentage_fifteen/100);
            $this->page->percentage_thirty = $assignment['points_possible']*($session->percentage_thirty/100);
//
            $start_time = new \DateTime($session->start_at);
            $this->page->start_at = $start_time->format('H:i');
//            $fifteen = $start_time->add(new \DateInterval('PT15M'))->format('H:i');
////            echo json_encode($fifteen);
//            $thirty = $start_time->add(new \DateInterval('PT15M'))->format('H:i');
//
//            $this->page->start_at = $start_timeStr;
//            $this->page->fifteen_time = $fifteen;
//            $this->page->percentage_fifteen = $session->percentage_fifteen;
//            $this->page->thirty_time = $thirty;
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
        $id = $this->page->attendancerecordId;
        // convert string to integer
        $config = $this->getInstance($data['name']);
        // retrieve existing record
        //update record with new data coming from POST
        $config->name = $data['name'];
        $config->animate = intval($data['animate']);
        $config->size = intval($data['size']);
        $config->custom_css = trim(preg_replace('/\s+/', ' ',  $data['custom_css']));
        //TODO: must finish updating the rest of the fields in your table
        $config->save();
        // update original record
        return ['message'=>"Component settings have been updated",'success'=>1,'object'=>json_encode($config)];
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
        $assignment->description = "Attendance";
        $assignment->assignment_group_id = $assignmentGrp->assignment_group_id;
        $assignment->course_id = $courseId;
        $assignment->published = true;
        $assignment->submission_types = ["online_text_entry"];

        $date = new \DateTime('now');
        $intervalString = "PT{$data['duration_minutes']}M";
        $date->add(new \DateInterval($intervalString));

        $assignment->due_at = $date;

        $req = new AssignmentsRequest(ActionType::POST, null, null, $assignment);
        $createdAssignment = $roots->assignments($req);


        //create the session with the assignment id, code, etc.
        $session = new Sessions();
        $session->course_id = $courseId;
        $session->assignment_id = $createdAssignment->assignment_id;
        $session->title = $data['title'];
        $session->start_at = new \DateTime('now');
        $session->duration_minutes = $data['duration_minutes'];
        $session->percentage_fifteen = $data['fifteen'];
        $session->percentage_thirty = $data['thirty'];
        $session->code = $this->generateCode();
        $session->save();

        return ['message'=>"Session successfully created",'success'=>1];
    }

    private function getAssignmentGroup($courseId)
    {
        $assignmentGrp = null;
        $include_assignments = false;
        $fresh_data = true;
        $assignmentGpId = null;
        $req = new AssignmentGroupsRequest(ActionType::GET, $include_assignments, $assignmentGpId, $fresh_data);

        $roots = new Roots();
        $groups = $roots->assignmentGroups($req);
        foreach($groups as $group)
        {
            if($group->name == "Attendance")
            {
                $assignmentGrp = $group;
            }
        }
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
        $rootUrl= base_path();

        $fullUrl = $rootUrl."/plugins/delphinium/orchid/assets/js/attendance_words.csv";
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

        //get the assignment for this session, so we can get the points

        $assignment_id = $session->assignment_id;
        $req = new AssignmentsRequest(ActionType::GET, $assignment_id, false, null, false);

        $roots = new Roots();
        $assignment = $roots->assignments($req);

        //first we have to make a submission, then we have to grade it.
        $userId = $_SESSION['userID'];
        $studentIds = array($userId);
        $assignmentIds = array($session->assignment_id);
        $multipleStudents = false;
        $multipleAssignments = false;
        $allStudents = false;
        $allAssignments = false;

        $params[] = "submission[submission_type]=online_text_entry";//TODO: get the type of assignment based on the type of assignment created
        $params[] = "submission[body]=Present";

        $req = new SubmissionsRequest(ActionType::POST, $studentIds, $allStudents,
            $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);


        $res = $roots->submissions($req, $params);


        //compare the started_at date of the assignment and 'now'
        $n = new \DateTime('now');
        $now = strtotime($n->format('c'));
        $s = new \DateTime($session->start_at);
        $start_at =strtotime($s->format('c'));
        $minutes = ($now-$start_at)/60;
        $points = floatval($assignment['points_possible']);
        $grade = $points;

        switch ($minutes)
        {
            case $minutes>=30:
                $grade = $points*($session->percentage_thirty/100);
                break;
            case $minutes>=15:
                $grade = $points*($session->percentage_fifteen/100);
                break;
            default:

        }

        $newreq = new SubmissionsRequest(ActionType::PUT, $studentIds, $allStudents,
            $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);

        $params[] = "submission[posted_grade]={$grade}";
        $roots = new Roots();
        $res = $roots->submissions($newreq, $params);
//        return $res;
        if($res&&isset($res->score))
        {
            return ['message'=>"Your attendance has been recorded",'success'=>1];
        }
        else
        {
            return ['message'=>"An error has occurred. Please inform your instructor.",'success'=>01];
        }

    }

    public function getActiveSession()
    {
        //BOTH TEACHER AND STUDENT
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        //check to see if there's an active session.
        $lastSession = Sessions::where(array('course_id' => $courseId))->orderBy('id', 'DESC')->first();
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
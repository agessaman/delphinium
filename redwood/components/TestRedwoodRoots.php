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

namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\RedwoodRoots;
use Delphinium\Redwood\Exceptions\InvalidRequestException;
use Delphinium\Roots\Classes\OAuthConsumer;
use Delphinium\Roots\Classes\OAuthRequest;
use Delphinium\Roots\Classes\OAuthSignatureMethodHMAC;

class TestRedwoodRoots extends ComponentBase
{
    public $roots;

    public function componentDetails()
    {
        return [
            'name'        => 'testredwoodroots Component',
            'description' => 'Playground for RedwoodRoots'
        ];
    }

    public function onRun()
    {
        $this->roots = new RedwoodRoots(1);
//        $this->test();
//        $this->testGetUsers();
//        $this->testCreateUser();
//        $this->testGetDepartments();
//        $this->testCreateDepartment();
//        $this->testGetGroup();
//        $this->testCreateGroup();
//        $this->testGetRoles();
//        $this->testPeerReviewWorkflow();
//        $this->testLoginUser();
//        $this->testIsUserInGroup();
//        $this->testGetProjects();
//        var_dump($_POST);
        $this->testGetStartingTask();
//        $this->testAssignGroupToTask();
//        $this->testGetTaskAssignees();
    }

    public function test()
    {
        echo json_encode($this->roots->getUsers());
    }

    public function testGetUsers()
    {
        $canvas_user_id = 1604486;
//        $res = $this->roots->getUsers();
        $res = $this->roots->getUsers($canvas_user_id);
        echo json_encode($res);
    }

    public function testCreateUser()
    {
        //we'd look through the roles and figure out which role we need
        //$roles = $this->testGetRoles();

        $first_name = "Dummy";
        $last_name = "Tra";
        $canvas_user_id = 1114325;
        $email = $canvas_user_id.'@uvlink.uvu.edu';//TODO: figure out a dynamic way to do the email
        $pm_role = "PROCESSMAKER_OPERATOR";
        $newUser = $this->roots->createUser($first_name, $last_name, $canvas_user_id, $email, $pm_role);
        echo json_encode($newUser);
        return $newUser;
    }

    public function testCreateDepartment()
    {
        $unique_department_name = "department"+time();
        $department_parent = "40673828156bbb673d391a0047310329";
        $department_manager = "00000000000000000000000000000001";
        $department_status="ACTIVE";
        $res = ($this->roots->createDepartment($unique_department_name,$department_parent,$department_manager,$department_status));
        echo json_encode($res);
    }

    public function testGetDepartments()
    {
        $pm_department_id = "40673828156bbb673d391a0047310329";
        $canvas_course_id = 343331;
        $res=($this->roots->getDepartments());
//        $res = ($this->roots->getDepartments($pm_department_id));//by department id
//        $res=($this->roots->getDepartments(null,$canvas_course_id));//by canvas course id
        echo json_encode($res);
    }

    public function testGetGroup()
    {
        $assignmentId = 1660429;
//        $assignmentId = 5432;
        $group = $this->roots->getGroups();//get all groups
//        $group = $this->roots->getGroups($assignmentId);//get a specific assignment aka group
        echo json_encode($group);
        return $group;
    }

    public function testCreateGroup()
    {
        $assignmentId = 1660429;
        $group = $this->roots->createGroup($assignmentId);
        echo json_encode($group);
        return $group;
    }

    public function testGetRoles()
    {
        $res = $this->roots->getRoles();
        echo json_encode($res);
        return $res;
    }

    public function testPeerReviewWorkflow()
    {
        $courseId = 343331;
        $assignmentId = 1660429;
        $studentId = 1604486;

        //try to get a department with the given course ID. If not found, create one
        $depts = $this->roots->getDepartments(null,$courseId);
        $courseAsDepartment= null;
        if(count($depts)<1){
            $courseAsDepartment = ($this->roots->createDepartment($courseId));
        }
        else{
            $courseAsDepartment = $depts[0];
        }


        //try to get a group with the given assignment ID. If not found, create one$assignmentId = 1660429;
        $groups = $this->roots->getGroups($assignmentId);
        $assignmentAsGroup=null;
        if(count($groups)<1){
            $assignmentAsGroup =$this->roots->createGroup($assignmentId);
        }
        else{
            $assignmentAsGroup=$groups[0];
        }

        //try to get a student. If not found on PM, create a new one
        $users = $this->roots->getUsers($studentId);
        if(count($users)<1)
        {
            //$this->roots->createUser($first_name, $last_name, $canvaS_user_id);
        }
        echo json_encode($users);
    }

    public function testLoginUser()
    {
        $studentId = 123456;
        $users = $this->roots->getUsers($studentId);
        if(count($users)<1)
        {
            $user = $this->roots->createUser("Test", "User", $studentId, $studentId."@uvu.edu","PROCESSMAKER_OPERATOR");
            array_push($users,$user);
        }
        if(count($users)>0)
        {
            $response = $this->roots->loginUser($users[0]->usr_username,$users[0]->usr_username);
            var_dump($response);
        }
    }

    public function testIsUserInGroup()
    {
        $user = $this->testCreateUser();
        $group = $this->testCreateGroup();
        $this->roots->assignUserToGroup($group->grp_uid,$user->usr_uid);

        $inGroup =  $this->roots->isUserInGroup($group->grp_uid, $user->usr_uid);
        if($inGroup)
        {
            echo "In Group";
        }
        else{
            echo "Not in group!";
        }
    }

    private function testGetProjects()
    {
        $projects = $this->roots->getProjects();
        var_dump($projects);
        return $projects;
    }

    private function testGetStartingTask()
    {
        $project_uid = '73043823256b50f6624af93018053833';
        $proj = $this->testGetProjects();
        if(count($proj)>0)
        {
//            $project_uid = $proj[0]->prj_uid;
            $startingTask = $this->roots->getStartingTask($project_uid);
            var_dump($startingTask);
            return $startingTask;
        }
        else{
            echo "No projects available";
        }
    }

    private function testAssignGroupToTask()
    {
        $proj = $this->testGetProjects();
        $groups = $this->testGetGroup();
//echo json_encode($groups);return;
        if(count($proj)>0 && count($groups)>0) {

//        $group_id = $groups[0]->grp_uid;
            $group_id = '64035034156d618bb9602b3076445049';

//            $project_id = $proj[0]->prj_uid;
            $project_id = '73043823256b50f6624af93018053833';
            $startingTask = $this->roots->getStartingTask($project_id);
            if(count($startingTask)>0)
            {
                $task_uid = $startingTask[0]->act_uid;
                $this->roots->assignGroupToTask($project_id,$task_uid , $group_id);
            }
        }
    }

    private function testGetTaskAssignees()
    {
        $project_uid = '73043823256b50f6624af93018053833';
        $activity_uid = '96176147456b510867fa071036708730';
        $result = $this->roots->getTaskAssignees($project_uid, $activity_uid);
        var_dump($result);
        return $result;
    }
}
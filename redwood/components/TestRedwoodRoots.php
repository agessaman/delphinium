<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\RedwoodRoots;
use Delphinium\Redwood\Exceptions\InvalidRequestException;

class TestRedwoodRoots extends ComponentBase
{
    public $roots;

    public function componentDetails()
    {
        return [
            'name'        => 'TestRedwoodRoots Component',
            'description' => 'Playground for RedwoodRoots'
        ];
    }

    public function onRun()
    {
        $this->roots = new RedwoodRoots();
//        $this->test();
//        $this->testGetUsers();
        $this->testCreateUser();
//        $this->testGetDepartments();
//        $this->testCreateDepartment();
//        $this->testGetGroup();
//        $this->testCreateGroup();
//        $this->testPeerReviewWorkflow();
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
        $first_name = "Tara";
        $last_name = "Jorgensen";
        $canvas_user_id = 1226308;
        $newUser = $this->roots->createUser($first_name, $last_name, $canvas_user_id);
        echo json_encode($newUser);
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
        $group = $this->roots->getGroups($assignmentId);//get a specific assignment aka group
        echo json_encode($group);
    }

    public function testCreateGroup()
    {
        $assignmentId = 1660429;
        echo json_encode($this->roots->createGroup($assignmentId));
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
}
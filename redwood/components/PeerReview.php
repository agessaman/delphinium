<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Config;
use Delphinium\Redwood\RedwoodRoots;
use Delphinium\Redwood\Models\OAuth as OAuthModel;

class PeerReview extends ComponentBase
{
    public $roots;
    public function componentDetails()
    {
        return [
            'name'        => 'PeerReview Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'configs' => [
                'title'             => 'OAuth Configuration',
                'description'       => 'Select the oauth configuration',
                'type'              => 'dropdown',
            ]
        ];
    }

    public function getConfigsOptions()
    {
        $instances = OAuthModel::get();

        $array_dropdown = ['0'=>'- select configuration - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->name;
        }

        return $array_dropdown;
    }

    public function onRun()
    {
        var_dump($_POST);return;
        if(!isset($_POST['lis_outcome_service_url']))
        {
            print "The peer review tool must be launched inside of your LMS. Add it as an assignment of the type 'External Tool'";
            return;
        }

        $this->roots = new RedwoodRoots($this->property('configs'));
        $assignmentId = $_POST['custom_canvas_assignment_id'];
        $canvas_login_id = $_POST['custom_canvas_user_login_id'];
        $courseId = $_POST['custom_canvas_course_id'];
        $gradebackUrl = $_POST['lis_outcome_service_url'];

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
        $users = $this->roots->getUsers($canvas_login_id);
        $givenUser=null;
        if(count($users)<1)
        {
            $first_name = $_POST['lis_person_name_given'];
            $last_name = $_POST['lis_person_name_family'];
            $user_email = $_POST['lis_person_contact_email_primary'];
            if(strlen($user_email)<1)
            {
                $user_email = "{$canvas_login_id}@uvlink.uvu.edu";//TODO: make this a more generic fall back
            }
            $canvasRoles = $_POST['roles'];
            $pm_role = $this->getPmRole($canvasRoles);
            $givenUser = $this->roots->createUser($first_name, $last_name, $canvas_login_id, $user_email, $pm_role);
        }
        else{
            $givenUser = $users[0];
        }

        //assign the user to the given group
        $this->roots->assignUserToGroup($assignmentAsGroup->grp_uid,$givenUser->usr_uid);



        //Once the user is created and assigned to the group, log them in and redirect them to process maker
        $loginResponse = $this->roots->loginUser($canvas_login_id,$canvas_login_id);
        if($loginResponse->status_code==0)
        {
            $pmServer = $this->roots->getRedirectUrl("PROCESSMAKER_OPERATOR");//students in Canvas are operators in processmaker
            $url = $pmServer."?sid={$loginResponse->message}";
            $this->redirect($url);
        }
        else{
            print "Unable to SSO into the Peer Review engine. Please inform your instructor";
        }
    }

    private function getPmRole($canvas_role)
    {
        switch($canvas_role)
        {
            case 'Learner':
                return "PROCESSMAKER_OPERATOR";
            case 'Instructor':
                return "PROCESSMAKER_MANAGER";
        }
    }

    function redirect($url) {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
        exit;
    }
}
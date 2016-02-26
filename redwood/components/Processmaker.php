<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Config;
use Delphinium\Redwood\RedwoodRoots;
use Delphinium\Redwood\Models\PMOAuth as OAuthModel;
use Delphinium\Redwood\Models\Processmaker as PmModel;
use Delphinium\Redwood\Controllers\Processmaker as PmController;

class Processmaker extends ComponentBase
{
    public $roots;
    public $instance;
    public function componentDetails()
    {
        return [
            'name'        => 'ProcessMaker Component',
            'description' => 'This component will connect to process maker'
        ];
    }

    public function defineProperties()
    {
        return [
            'copy'	=> [
                'title'             => 'Copy name',
                'description'       => 'Enter the name of this copy of the processmaker component',
                'type'              => 'string',
                'required'          => 'true',
                'validationMessage' => 'Please enter a copy name'
            ]
        ];
    }

    public function onRun()
    {
//        if(!isset($_POST['lis_outcome_service_url']))
//        {
//            echo "The peer review tool must be launched inside of your LMS. Add it as an assignment of the type 'External Tool'";
//            return;
//        }
//        if (!isset($_SESSION)) {
//            session_start();
//        }
//        if(!isset($_SESSION['pm_credentials_id']))
//        {
//            echo "Session variables not set. You must include the PMOauth component on this page";
//            return;
//        }

        //grab the instance for this course, or create a new one if it doesn't exist
        $instance = $this->firstOrNewCourseInstance();
        $this->instance = $instance;
        $this->page['instance'] = $instance;

        //set up everything in process maker
        $credentials_id = $_SESSION['pm_credentials_id'];
        $this->roots = new RedwoodRoots($credentials_id);
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
        $canvasRoles = $_POST['roles'];
        $pm_role = $this->roots->getPmRole($canvasRoles);
        if(count($users)<1)
        {
            $first_name = $_POST['lis_person_name_given'];
            $last_name = $_POST['lis_person_name_family'];
            $user_email = $_POST['lis_person_contact_email_primary'];
            if(strlen($user_email)<1)
            {
                $user_email = "{$canvas_login_id}@uvlink.uvu.edu";//TODO: make this a more generic fall back
            }
            $givenUser = $this->roots->createUser($first_name, $last_name, $canvas_login_id, $user_email, $pm_role);
        }
        else{
            $givenUser = $users[0];
        }

        //if user is not assigned to group, assign him/her
        $userInGroup = $this->roots->isUserInGroup($assignmentAsGroup->grp_uid, $givenUser->usr_username);
        if(count($userInGroup)<1)
        {
            $this->roots->assignUserToGroup($assignmentAsGroup->grp_uid,$givenUser->usr_uid);
        }

        //redirect instructors and students to their corresponding places
        $roleStr = $_POST['roles'];
        $this->page['role'] = $roleStr;

        if(stristr($roleStr, 'Instructor')||(stristr($roleStr, 'TeachingAssistant')))
        {
            $this->instructor();
        }
        else if(stristr($roleStr, 'Learner'))
        {
            $this->student($canvas_login_id,$pm_role);
        }

    }


    private function instructor()
    {
        $this->addCss("/plugins/delphinium/redwood/assets/css/pm_professor.css");
        $this->addJs("/plugins/delphinium/redwood/assets/js/pm_professor.js");
        //get a list of all processes available
        $projects = $this->roots->getProjects();
        $this->page['processes']= $projects;
        $process_name = null;
        foreach($projects as $project)
        {
            if($project->prj_uid == $this->instance->process_id)
            {
                $process_name= $project->prj_name;
            }
        }

        $this->page['valid_process'] = is_null($process_name)?0:1;
    }

    private function student($canvas_login_id,$pm_role)
    {
        //Once the user is created and assigned to the group, log them in and redirect them to process maker
        $loginResponse = $this->roots->loginUser($canvas_login_id,$canvas_login_id);
        if($loginResponse->status_code==0)
        {
            $pmServer = $this->roots->getRedirectUrl($pm_role);//students in Canvas are operators in processmaker. Teachers are managers
            $url = $pmServer."?sid={$loginResponse->message}";
            $this->redirect($url);
        }
        else{
            print "Unable to log student into ProcessMaker. Please inform your instructor";
        }
    }

    /**
     * update, add course_id
     * save to database and return updated
     */
    public function onSave()
    {
        $res = new \stdClass();
        $obj =  post('obj');

        if(is_null(post('instance_id'))||is_null($obj))
        {
            $res->code=0;
            $res->message = "Instance id or object not set";
            return $res;
        }

        $pm = PmModel::where('id','=',  post('instance_id'))->first();
        $pm->course_id = $obj['course_id'];
        $pm->process_id = $obj['process_id'];
        $pm->copy_name = $obj['copy_name'];
        $pm->save();

        $this->page['instance']=$pm;

        $res->code=1;
        $res->message = "Success";
        $res->instance = $pm;
        return json_encode($res);
        Flash::success('Saved!');
    }


    private function gradePostback($value)
    {
        if(!isset($_POST['lis_result_sourcedid']))
        {
            echo "Grade passback only works in student mode";
            return;
        }
        $sourceId = $_POST['lis_result_sourcedid'];
        $url = $_POST['lis_outcome_service_url'];
        $oauth_consumer_key = $_POST['oauth_consumer_key'];
        return $this->roots->gradePostback($sourceId, $url, $value, $oauth_consumer_key);
    }

    private function firstOrNewCourseInstance()
    {
        $courseId = $_POST['custom_canvas_course_id'];
        $courseInstance =PmModel::firstOrNew(array('course_id' => $courseId));
        $courseInstance->course_id = $courseId;
        if(is_null($courseInstance->copy_name)){$courseInstance->copy_name=$this->property('copy');}
        if(is_null($courseInstance->process_id)){$courseInstance->process_id = 1;}
        $courseInstance->save();

        return $courseInstance;
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
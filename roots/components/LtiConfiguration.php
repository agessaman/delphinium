<?php

namespace Delphinium\Roots\Components;

use Delphinium\Roots\Models\Developer as LtiConfigurations;
use Delphinium\Roots\Models\User;
use Delphinium\Roots\Models\UserCourse;
use Cms\Classes\ComponentBase;
use Delphinium\Roots\Classes\Blti;
use Delphinium\Roots\Roots;
use Delphinium\Roots\DB\DbHelper;
use Config;
use Carbon\Carbon;
use Delphinium\Roots\Exceptions\NonLtiException;

class LtiConfiguration extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'LTI Configuration Component',
            'description' => 'Handles the LTI Configuration required for communicating with Canvas'
        ];
    }

    public function onRun() {
        try
        {
            $this->doBltiHandshake();
        }
        catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
        {
            return \Response::make($this->controller->run('error'), 500);
        }
        catch(NonLtiException $e)
        {
            if($e->getCode()==584)
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            else{
                echo json_encode($e->getMessage());return;
            }
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
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

    public function defineProperties() {
        return [
            'ltiInstance' => [
                'title' => 'LTI Instance',
                'description' => 'Select the LTI configuration instance to use for connecting to Canvas',
                'type' => 'dropdown',
            ],
            'approver' => [
                'title' => 'Approver',
                'description' => 'The approver must have the right permissions to access the data needed for this component',
                'type' => 'dropdown',
                'default' => 'Instructor',
            ]
        ];
    }

    public function getLtiInstanceOptions() {
        $instances = LtiConfigurations::all();
        $array_dropdown = ['0' => '- select an LTI configuration - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

    public function getApproverOptions() {
        $arr = array(
            "0" => "Instructor",
            "1" => "Administrator",
            "2" => "Student"
        );
        return $arr;
    }

    public function doBltiHandshake() {
        //first obtain the details of the LTI configuration they chose
        $dbHelper = new DbHelper();
        $instanceFromDB = LtiConfigurations::find($this->property('ltiInstance'));
        $approver = $this->property('approver');
        $arr = $this->getApproverOptions();
        $approverRole = $arr[$approver];

        if (!isset($_SESSION)) {
            session_start();
        }
        echo "POST:<br>";
        var_dump($_POST);
        echo "GET:<br>";
        var_dump($_GET);
        $_SESSION['baseUrl'] = Config::get('app.url', 'backend');
        $_SESSION['courseID'] = \Input::get('custom_canvas_course_id');
        $_SESSION['userID'] = \Input::get('custom_canvas_user_id');
        echo "User ID: <br>";
        var_dump($_SESSION['userID']);die;
        $_SESSION['domain'] = '185.44.229.29:3000';//\Input::get('custom_canvas_api_domain');
        //get the roles
        $roleStr = \Input::get('roles');
        if(stristr($roleStr,'Learner')||stristr($roleStr,'Instructor'))
        {
            $_SESSION['roles'] = $roleStr;
        }
        else
        {
            $parts = explode("lis/", $roleStr);
            if(count($parts)>=2)
            {
                $_SESSION['roles'] = ($parts[1]);
            }
        }
        //TODO: make sure this parameter below works with all other LMSs
        $_SESSION['lms'] = \Input::get('tool_consumer_info_product_family_code');

        //check to see if user is an Instructor
        $rolesStr = \Input::get('roles');
        $secret = $instanceFromDB['SharedSecret'];
        $clientId = $instanceFromDB['DeveloperId'];

        //Check to see if the lti handshake passes
        $context = new Blti($secret, false, false);


        if ($context->valid) { // query DB to see if user has token, if yes, go to LTI.

            $userCheck = $dbHelper->getCourseApprover($_SESSION['courseID']);
            if (!$userCheck) { //if no user is found, redirect to canvas permission page
                if (stristr($rolesStr, $approverRole)) {
                    //As per my discussion with Jared, we will use the instructor's token only. This is the token that will be stored in the DB
                    //and the one that will be used to make all requests. We will NOT store student's tokens.
                    //TODO: take this redirectUri out into some parameter somewhere...

                    $baseUrlWithSlash = rtrim($_SESSION['baseUrl'], '/') . '/';
                    $domainWithSlash = rtrim($_SESSION['domain'], '/') . '/';

                    $redirectUri = "{$baseUrlWithSlash}saveUserInfo?lti={$this->property('ltiInstance')}";
                    $url = "http://{$domainWithSlash}login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
                    $this->redirect($url);
                } else {
                    echo ("A(n) {$approverRole} must authorize this course. Please contact your instructor.");
                    return;
                }
            } else {

                //set the professor's token
                $_SESSION['userToken'] = $userCheck->encrypted_token;
                //get the timezone
                $roots = new Roots();
                $course = $roots->getCourse();
                $account_id = $course->account_id;
                $account = $roots->getAccount($account_id);
                $courseId =$_SESSION['courseID'];

                $_SESSION['timezone'] = new \DateTimeZone($account->default_time_zone);
                //to maintain the users table synchronized with Canvas, everytime a student comes in we'll check to make sure they're in the DB.
                //If they're not, we will pull all the students from Canvas and refresh our users table.
                $dbHelper = new DbHelper();
                $user = $dbHelper->getUserInCourse($courseId, $_SESSION['userID']);
                if(is_null($user))
                {//get all students from Canvas
                    $roots = new Roots();
                    $users = $roots->getStudentsInCourse();

                }

                //Also, every so often (every 12 hrs?) we will check to make sure that students who have dropped the class are deleted from the users_course table
                //Failing to do so will make it so that when we request their submissions along with other students' submissions, the entire
                // call returns with an Unauthorized error message
                $approver = $dbHelper->getCourseApprover($courseId);
                $now = Carbon::now();
                $updatedDate = $approver->updated_at;

                $diff = $updatedDate->diffInHours($now, false);
                if($diff>24)
                {
                    $allStudentsDb = $dbHelper->getUsersInCourseWithRole($_SESSION['courseID'], 'Learner');
                    $allStudentsFromCanvas = $roots->getStudentsInCourse();
                    foreach($allStudentsDb as $dbStudent)
                    {
                        $filteredItems = array_values(array_filter($allStudentsFromCanvas, function($elem) use($dbStudent) {
                            return intval($elem->user_id) === intval($dbStudent->user_id);
                        }));

                        if(count($filteredItems)<1)//meaning they are in our DB but they are not in Canvas anymore
                        {
                            $dbHelper->deleteUserFromRole($courseId, $dbStudent->user_id, 'Learner');
                        }
                    }

                    //update the approver
                    $approver->updated_at = $now;
                    $approver->save();
                }
            }
        } else {
            echo('There is a problem. Please notify your instructor');
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

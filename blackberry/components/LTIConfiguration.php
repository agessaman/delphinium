<?php namespace Delphinium\Blackberry\Components;

use Delphinium\Blackberry\Models\Developer as LtiConfigurations;
use Delphinium\Blackberry\Models\User;
use Cms\Classes\ComponentBase;
use Delphinium\Blackberry\Classes\Blti;

class LTIConfiguration extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'LTI Configuration Component',
            'description' => 'Handles the LTI Configuration required for communicating with Canvas'
        ];
    }

    public function onRun() {
        $this->doBltiHandshake();
    }

    public function defineProperties() {
        return [
            'ltiInstance' => [
                'title' => 'LTI Instance',
                'description' => 'Select the LTI configuration instance to use for connecting to Canvas',
                'type' => 'dropdown',
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

    public function doBltiHandshake() {
	//first obtain the details of the LTI configuration they chose
        $instanceFromDB = LtiConfigurations::find($this->property('ltiInstance'));

        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
    	
        $_SESSION['courseID'] = $_POST["custom_canvas_course_id"];
        $_SESSION['userID'] = $_POST['custom_canvas_user_id'];
        $_SESSION['domain'] = $_POST['custom_canvas_api_domain'];
        //check to see if user is an Instructor
        $rolesStr = $_POST['roles'];
        
            $consumerKey = $instanceFromDB['ConsumerKey'];
            $clientId = $instanceFromDB['DeveloperId'];

            //Check to see if the lti handshake passes
            $context = new Blti($consumerKey, false, false);

            if ($context->valid) 
            { // query DB to see if user has token, if yes, go to LTI.

                $userCheck = User::where('course_id', $_SESSION['courseID'])->first();

                if (!$userCheck ) //if no user is found, redirect to canvas permission page
                {
                    if(strstr('Instructor', $rolesStr)) //but only if it's an instructor. 
                    //As per my discussion with Jared, we will use the instructor's token only. This is the token that will be stored in the DB
                    //and the one that will be used to make all requests. We will NOT store student's tokens.
                    { 
                        //TODO: take this redirectUri out into some parameter somewhere...
                        $redirectUri = "https://delphinium.uvu.edu/octobercms/saveUserInfo?lti={$this->property('ltiInstance')}";
                        $url = "https://{$_SESSION['domain']}/login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
                        $this->redirect($url);
                    }
                    else
                    {
                        echo ("Your Instructor must authorize this course. Please contact your instructor.");
                        return;
                    }
                } 
                else 
                {
                    $_SESSION['userToken'] = $userCheck->encrypted_token;
                }
            } 
            else 
            {
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

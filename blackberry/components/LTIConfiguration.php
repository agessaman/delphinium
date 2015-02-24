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

        session_start();
        $_SESSION['courseID'] = $_POST["custom_canvas_course_id"];
        $_SESSION['userID'] = $_POST['custom_canvas_user_id'];
        $_SESSION['domain'] = $_POST['custom_canvas_api_domain'];
        //check to see if user is an Instructor
        $rolesStr = $_POST['roles'];
        
        //as per my discussion with Jared, we will use the instructor's token only. This is the token that will be stored in the DB
        //and the one that will be used to make all requests. We will NOT store student's tokens.
        if (strstr('Instructor', $rolesStr)) 
        {
            $consumerKey = $instanceFromDB['ConsumerKey'];
            $clientId = $instanceFromDB['DeveloperId'];

            //Check to see if the lti handshake passes
            $context = new Blti($consumerKey, false, false);

            if ($context->valid) 
            {
                // query DB to see if user has token, if yes, go to LTI.
                $userCheck = User::where('user_id', $_SESSION['userID'])->first();

                if (!$userCheck) //if no user is found, redirect to canvas permission page
                {
                    //TODO: take this redirectUri out into some parameter somewhere...
                    $redirectUri = "https://delphinium.uvu.edu/octobercms/saveUserInfo?lti={$this->property('ltiInstance')}";
                    $url = "https://{$_SESSION['domain']}/login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
                    $this->redirect($url);
                } 
                else 
                {
                    // echo 'This is your userId: '.$_SESSION['userID']. PHP_EOL;
                    // echo 'This is your token: '. $userCheck->encrypted_token. PHP_EOL;
                    // echo 'This is the courseId: '.$_SESSION['courseID']. PHP_EOL;
                    // echo 'This is the domain: '.$_SESSION['domain'];
                    $_SESSION['userToken'] = $userCheck->encrypted_token;
                    //DON'T REDIRECT, BECAUSE THIS PLUGIN WILL BE USED BY ALL OTHER PLUGINS, AND WE MUST NOT REDIRECT THEM ANYWHERE.
                    //THIS WILL BE DETERMINED BY THE OTHER PLUGINS
                }
            } 
            else 
            {
                echo('There is a problem. Please notify your instructor');
            }
        } 
        else 
        {
            echo ("Your Instructor must authorize this course. Please contact your instructor.");
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

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

namespace Delphinium\Dev\Components;

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

class Data extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Data Component',
            'description' => 'This component will handle the LTI handshake and display the data users need to configure an instance of the dev component'
        ];
    }

    public function onRun()
    {
        try {
            $this->doBltiHandshake();
        } catch (NonLtiException $e) {
            if ($e->getCode() == 584) {
                return \Response::make($this->controller->run('nonlti'), 500);
            } else {
                echo json_encode($e->getMessage());
                return;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Invalid LMS') {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
    }

    public function defineProperties()
    {
        return [
            'ltiInstance' => [
                'title' => 'LTI Instance',
                'description' => 'Select the LTI configuration instance to use for connecting to Canvas',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getLtiInstanceOptions()
    {
        $instances = LtiConfigurations::all();
        $array_dropdown = ['0' => '- select an LTI configuration - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

    public function doBltiHandshake()
    {
        //first obtain the details of the LTI configuration they chose
        $dbHelper = new DbHelper();
        $instanceFromDB = LtiConfigurations::find($this->property('ltiInstance'));

        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['baseUrl'] = Config::get('app.url', 'backend');
        $_SESSION['courseID'] = \Input::get('custom_canvas_course_id');
        $_SESSION['userID'] = \Input::get('custom_canvas_user_id');
        $_SESSION['domain'] = \Input::get('custom_canvas_api_domain');


        //get the roles
        $roleStr = \Input::get('roles');
        if (stristr($roleStr, 'Learner')) {
            $_SESSION['roles'] = $roleStr;
        } else {
            $parts = explode("lis/", $roleStr);
            if (count($parts) >= 2) {
                $_SESSION['roles'] = ($parts[1]);
            }
        }
        //TODO: make sure this parameter below works with all other LMSs
        $_SESSION['lms'] = \Input::get('tool_consumer_info_product_family_code');

        //check to see if user is an Instructor
        $rolesStr = \Input::get('roles');
        $secret = $instanceFromDB['SharedSecret'];
        $consumerKey = $instanceFromDB['ConsumerKey'];
        $clientId = $instanceFromDB['DeveloperId'];

        //Check to see if the lti handshake passes
        $context = new Blti($secret, false, false);


        if ($context->valid) { // query DB to see if user has token, if yes, go to LTI.

            $userCheck = $dbHelper->getCourseApprover($_SESSION['courseID']);
            if (!$userCheck) { //if no user is found, redirect to canvas permission page
                if (stristr($rolesStr, 'Instructor')) {
                    //As per my discussion with Jared, we will use the instructor's token only. This is the token that will be stored in the DB
                    //and the one that will be used to make all requests. We will NOT store student's tokens.
                    //TODO: take this redirectUri out into some parameter somewhere...
                    $baseUrlWithSlash = rtrim($_SESSION['baseUrl'], '/') . '/';
                    $domainWithSlash = rtrim($_SESSION['domain'], '/') . '/';

                    $redirectUri = "{$baseUrlWithSlash}saveUserInfo?lti={$this->property('ltiInstance')}";
                    $url = "https://{$domainWithSlash}login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";

                    $this->redirect($url);
                } else {
                    echo("An error has occurred. Please contact your instructor.");
                    return;
                }
            } else {

                $_SESSION['userToken'] = $userCheck->encrypted_token;
                $decrypted = \Crypt::decrypt($userCheck->encrypted_token);
                //get the timezone
                $roots = new Roots();
                $course = $roots->getCourse();
                $account_id = $course->account_id;
                $account = $roots->getAccount($account_id);
                $courseId = $_SESSION['courseID'];
                $_SESSION['timezone'] = new \DateTimeZone($account->default_time_zone);
                echo nl2br("User Id: {$_SESSION['userID']} \n");
                echo nl2br("Token: {$decrypted} \n");
                echo nl2br("Course Id: {$_SESSION['courseID']} \n");
                echo nl2br("LMS: Canvas \n");
                echo nl2br("Domain: {$_SESSION['domain']} \n");
                echo nl2br("Role(s): {$_SESSION['roles']} \n");
                $timez = $_SESSION['timezone']->getName();
                echo nl2br("Timezone: " . ($timez));

            }
        } else {
            echo('There is a problem. Please notify your instructor');
        }
    }

    function redirect($url)
    {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
        exit;
    }
}
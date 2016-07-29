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

namespace Delphinium\Roots\Controllers;

use \Input;
use Illuminate\Routing\Controller;
use Delphinium\Roots\Models\Developer as LtiConfigurations;
use Delphinium\Roots\Models\User;
use Delphinium\Roots\Models\UserCourse;
use Delphinium\Roots\Models\Role;
use Delphinium\Roots\Roots;
use Delphinium\Roots\DB\DbHelper;

class OAuthResponse extends Controller {

    public function saveUserInfo()
    {
        if (!isset($_SESSION))
        {
            session_start();
        }
        $code = Input::get('code');
        $lti = Input::get('lti');
        $roleId = Input::get('role');
        $approver = Input::get('approver');
        if(is_null($code))//meaning, they cancelled rather than authorize the LTI app
        {
            echo "You have canceled authorizing this app. If you want to use this app, you must authorize it. Please reload this page.";
            return;
        }

        $instanceFromDB = LtiConfigurations::find($lti);

        $clientId = $instanceFromDB['developer_id'];
        $developerSecret = $instanceFromDB['developer_secret'];

        $opts = array('http' => array('method' => 'POST',));
        $context = stream_context_create($opts);
        $url = "https://{$_SESSION['domain']}/login/oauth2/token?client_id={$clientId}&client_secret={$developerSecret}&code={$code}";
        $userTokenJSON = file_get_contents($url, false, $context, -1, 40000);
        $userToken = json_decode($userTokenJSON);

        $actualToken = $userToken->access_token;
        $encryptedToken = \Crypt::encrypt($actualToken);

        $dbHelper = new DbHelper();
        $role = $dbHelper->getRoleById($roleId);

        $newRoleId = $roleId;
        if($role->role_name===$approver)
        {
            $_SESSION['userToken'] = $encryptedToken;
            $newRoleId = $dbHelper->getRole('Approver')->id;
        }
        else
        {
            switch($roleId)
            {
                case 1://student
                    $_SESSION['studentToken'] = $encryptedToken;
                    break;
                case 2://TA
                    $_SESSION['taToken'] = $encryptedToken;
                    break;
                case 3://instructor
                    $_SESSION['instructorToken'] = $encryptedToken;
                    break;
                case 4://approver (may be an instructor, an admin, or whatever the user configured in the LTIConfiguration component
                    $_SESSION['userToken'] = $encryptedToken;
                    break;
            }
        }

        $roleId = $newRoleId;
        setcookie("token_attempts", 0, time() + (300), "/"); //5 minutes

        //store encrypted token in the database
        $courseId = $_SESSION['courseID'];
        $userId = $_SESSION['userID'];
        $userCourse = UserCourse::firstOrNew(array('user_id' => $userId, 'course_id' => $courseId));
        $userCourse->user_id = $userId;
        $userCourse->course_id = $courseId;
        $userCourse->role = $roleId;
        $userCourse->encrypted_token = $encryptedToken;
        $userCourse->save();

        echo "App has been approved. Please reload this page";
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

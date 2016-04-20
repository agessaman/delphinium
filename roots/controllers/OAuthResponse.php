<?php

namespace Delphinium\Roots\Controllers;

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
        $code = \Input::get('code');
        $lti = \Input::get('lti');

        $instanceFromDB = LtiConfigurations::find($lti);

        $clientId = $instanceFromDB['DeveloperId'];
        $developerSecret = $instanceFromDB['DeveloperSecret'];

        $opts = array('http' => array('method' => 'POST',));
        $context = stream_context_create($opts);
        //$url = "http://{$_SESSION['domain']}/login/oauth2/token?client_id={$clientId}&client_secret={$developerSecret}&code={$code}";
        //$userTokenJSON = file_get_contents($url, false, $context, -1, 40000);
        $userTokenJSON = shell_exec('curl --data "client_id='.$clientId.'&client_secret='.$developerSecret.'&code='.$code.'" http://'.$_SESSION['domain'].'/login/oauth2/token');

        // $ch = curl_init();
        // $postvars = "client_id={$clientId}&client_secret={$developerSecret}&code={$code}";
        // $url = "http://{$_SESSION['domain']}/login/oauth2/token";
        // curl_setopt($ch,CURLOPT_URL,$url);
        // curl_setopt($ch, CURLOPT_PORT , 3000);
        // curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
        // curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
        // curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,5);
        // curl_setopt($ch,CURLOPT_TIMEOUT, 20);
        // $userTokenJSON = curl_exec($ch);
        // curl_close ($ch);
        
        $userToken = json_decode($userTokenJSON);

        $actualToken = $userToken->access_token;
        $encryptedToken = \Crypt::encrypt($actualToken);
        $_SESSION['userToken'] = $encryptedToken;

        //store encrypted token in the database
        $courseId = $_SESSION['courseID'];
        $userId = $_SESSION['userID'];

        //make sure we have the user stored in the user table and in the userCourse table.
        $roots = new Roots();
        //when we get the user from the LMS it gets stored in the DB.
        $roots->getUser($userId);
        $dbHelper = new DbHelper();
        $role = $dbHelper->getRole('Approver');
        
        $userCourse = UserCourse::firstOrNew(array('user_id' => $userId, 'course_id' => $courseId));
        $userCourse->user_id = $userId;
        $userCourse->course_id = $courseId;
        $userCourse->role = $role->id;
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

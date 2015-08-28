<?php

namespace Delphinium\Roots\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Roots\Models\Developer as LtiConfigurations;
use Delphinium\Roots\Models\User;

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
        $url = "https://{$_SESSION['domain']}/login/oauth2/token?client_id={$clientId}&client_secret={$developerSecret}&code={$code}";
        $userTokenJSON = file_get_contents($url, false, $context, -1, 40000);
        $userToken = json_decode($userTokenJSON);

        $actualToken = $userToken->access_token;
        $encryptedToken = \Crypt::encrypt($actualToken);
        $_SESSION['userToken'] = $encryptedToken;

        //store encrypted token in the database
        $courseId = $_SESSION['courseID'];
        $userId = $_SESSION['userID'];

        $user = new User();
        $user->user_id = $userId;
        $user->course_id = $courseId;
        $user->encrypted_token = $encryptedToken;
        $user->save();

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

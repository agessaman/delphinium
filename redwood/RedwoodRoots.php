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

namespace Delphinium\Redwood;


use Aws\CloudFront\Exception\Exception;
use Config;
use Delphinium\Redwood\Exceptions\InvalidRequestException;
use Delphinium\Redwood\Models\PMOAuth;
use Delphinium\Redwood\Models\Authorization;
use Delphinium\Roots\Classes\OAuthConsumer;
use Delphinium\Roots\Classes\OAuthRequest;
use Delphinium\Roots\Classes\OAuthSignatureMethodHMAC;

class RedwoodRoots
{
    public $credentials_id;
    public $pm_workspace;
    public $pm_server;
    public $access_token;
    public $refresh_token;

    function __construct($credentials_id)
    {
        $this->credentials_id = $credentials_id;
        if(!isset($_SESSION))
        {
            session_start();
        }
        $this->pm_server = $_SESSION['pm_server'];
        $this->pm_workspace = $_SESSION['pm_workspace'];
        $this->access_token = \Crypt::decrypt($_SESSION['pm_encrypted_access_token']);
        $this->refresh_token = \Crypt::decrypt($_SESSION['pm_encrypted_refresh_token']);
    }

    public function getUsers($canvas_user_id=null)
    {//GET /api/1.0/{workspace}/users?filter={filter}&start={start}&limit={limit}
        $endpoint = "users";
        $params = null;
        $returnArr =array();
        if (!is_null($canvas_user_id)) {
            $params = array(
                'filter'    => $canvas_user_id
            );
        }
        $users = $this->pmRestRequest("GET", $endpoint, $params);
        if(is_null($canvas_user_id))
        {
            if(is_array($users)){return $users;}
            else{
                array_push($returnArr,$users);
                return $returnArr;
            }
        }
        else{
            foreach($users as $user)
            {
                if($user->usr_username==strval($canvas_user_id))
                {
                    array_push($returnArr,$user);
                }
            }
            return $returnArr;
        }
    }

    /**
     * @param null $pm_dep_uid = the uid of a process maker department
     * @param null $canvas_course_id = The id of a canvas course
     * @return array an array with the requested department, or an empty array if no matches were found
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function getDepartments($pm_dep_uid = null, $canvas_course_id=null)
    {//GET /api/1.0/{workspace}/departments
        $endpoint = "departments";
        $returnArr = array();
        if (!is_null($pm_dep_uid)) {
            $endpoint = "{$endpoint}/{$pm_dep_uid}";
        }
        $departments = $this->pmRestRequest("GET", $endpoint);
        if(is_null($canvas_course_id))
        {
            if(is_array($departments)){return $departments;}
            else{
                array_push($returnArr,$departments);
                return $returnArr;
            }
        }
        else
        {
            foreach($departments as $department)
            {
                if($department->dep_title===strval($canvas_course_id))
                {
                    array_push($returnArr,$department);
                }
            }
            return $returnArr;
        }
    }

    public function getGroups($group_title=null)
    {//GET /api/1.0/{workspace}/groups?filter={filter}&start={start}&limit={limit}
        $endpoint = "groups";
        $params = null;
        $returnArr = array();
        if (!is_null($group_title)) {
            $params = array(
                'filter'    => $group_title
            );
        }

        $groups= $this->pmRestRequest("GET", $endpoint, $params);
        if(is_null($group_title))
        {
            if(is_array($groups)){return $groups;}
            else{
               array_push($returnArr,$groups);
            }
        }
        else{
            foreach($groups as $item)
            {
                if($item->grp_title===strval($group_title))
                {
                    array_push($returnArr,$item);
                }
            }
            return $returnArr;
        }
    }

    /**
     * @return mixed Returns an array of all projects in ProcessMaker
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function getProjects()
    {//GET /api/1.0/{workspace}/roles
        $projects = $this->pmRestRequest("GET", 'projects');
        $returnArr=array();
        if(count($projects)<1)
        {
            array_push($returnArr,$projects);
        }
        else
        {
            return $projects;
        }
    }

    public function getStartingTask($project_uid)
    {// GET /api/1.0/{workspace}/project/{pro_uid}/starting-tasks
        $result = $this->pmRestRequest("GET", "project/{$project_uid}/starting-tasks");
        return $result;
    }

    public function getTaskAssignees($project_uid, $activity_uid)
    {//GET/api/1.0/{workspace}/project/{prj_uid}/activity/{act_uid}/assignee?filter={string}&start={number}&limit={number}
        $result = $this->pmRestRequest("GET", "project/{$project_uid}/activity/{$activity_uid}/assignee");
        return $result;
    }
    /**
     * @return mixed An array with the roles available in ProcessMaker
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function getRoles()
    {//GET /api/1.0/{workspace}/roles
        $roles = $this->pmRestRequest("GET", 'roles');
        $returnArr=array();
        if(count($roles)<1)
        {
            array_push($returnArr,$roles);
        }
        else
        {
            return $roles;
        }
    }

    /**
     * @param $group_id The processMaker grp_uid of the group
     * @param $username The Process Maker username of the user
     * @return mixed An array containing the user, or an empty array if not found
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function isUserInGroup($group_id, $username)
    {// GET /api/1.0/{workspace}/group/{grp_uid}/users?filter={filter}&start={start}&limit={limit}
        $endpoint = "group/{$group_id}/users";
        $params = null;
        $params = array(
            'filter'    => $username
        );
        $users= $this->pmRestRequest("GET", $endpoint, $params);

        foreach($users as $user)
        {
            if(strval($user->usr_username)==strval($username) )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $canvas_role The role of the user in Canvas
     * @return string the ProcessMaker role
     */
    public function getPmRole($canvas_role)
    {
        switch($canvas_role)
        {
            case 'Instructor':
                return "PROCESSMAKER_MANAGER";
            case 'Learner':
            default:
                return "PROCESSMAKER_OPERATOR";
        }
    }
    /*
     * @param $unique_department_name The name of the department. Must be unique. By convention we will use the courseID as department name
     * @param null $department_parent Optional: Parent department's unique ID
     * @param null $department_manager Optional: Department supervisor's unique ID
     * @param $department_status Optional: Department status, which can be "ACTIVE" or "INACTIVE". If not included, then "ACTIVE" by default.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function createDepartment($unique_department_name, $department_parent = null, $department_manager = null, $department_status = null)
    {//POST /api/1.0/{workspace}/department

        $postParams = array(
            'dep_title'    => $unique_department_name,
            'dep_parent'   => $department_parent,
            'dep_manager'  => $department_manager,
            'dep_status' => $department_status
        );

        $result = $this->pmRestRequest("POST", "department", $postParams);
        return $result;
    }

    /**
     * @param $group_title The title of the group (must be the Canvas assignment Id)
     * @return mixed The group that was just created
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function createGroup($group_title)
    {//POST /api/1.0/{workspace}/group
        $postParams = array(
            'grp_title'    => $group_title,
            'grp_status'   => "ACTIVE"
        );

        $result = $this->pmRestRequest("POST", "group", $postParams);
        return $result;

    }

    /**
     * @param $first_name User's first name
     * @param $last_name User's last name
     * @param $canvas_user_id User's canvas login id (uvu id)
     * @param $email The preferred email for the user
     * @param $pm_role The role of the user
     * @return mixed A stdClass of the user object
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function createUser($first_name, $last_name, $canvas_user_id, $email, $pm_role)
    {//POST /api/1.0/{workspace}/user
        $postParams = array(
            'usr_username'    => $canvas_user_id,
            'usr_firstname'   => $first_name,
            'usr_lastname'  => $last_name,
            'usr_email'=>$email,
            'usr_due_date' =>'2020-12-31',
            'usr_status' =>'ACTIVE',
            'usr_role'=>$pm_role,
            'usr_new_pass'=>$canvas_user_id,
            'usr_cnf_pass'=>$canvas_user_id
            );

        if(!is_null($email)){
            $postParams['usr_email'] = $email;
        }
        if(!is_null($pm_role)){
            $postParams['usr_role'] = $pm_role;
        }

        $result = $this->pmRestRequest("POST", "user", $postParams);
        return $result;
    }

    /**
     * @param $project_uid The uid of the project in which a new task will be created
     * @param $task_uid The uid of a starting task within the project
     * @return mixed The newly created case
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function createCase($project_uid,$task_uid)
    {//POST /api/1.0/{workspace}/cases
        $postParams = array(
            'pro_uid'    => $project_uid,
            'tas_uid'=>$task_uid
        );

        $result = $this->pmRestRequest("POST", "cases", $postParams);
        return $result;
    }
    /**
     * @param $group_uid The processmaker uid of the group
     * @param $user_uid The processmaker uid of the user that will be added to the group
     * @return mixed
     * @throws Exception
     * @throws InvalidRequestException
     */
    public function assignUserToGroup($group_uid,$user_uid)
    {//POST /api/1.0/{workspace}/group/{grp_uid}/user
        $postParams = array(
            'usr_uid'    => $user_uid
        );

        $result = $this->pmRestRequest("POST", "group/{$group_uid}/user", $postParams);
        return $result;
    }

    public function assignUserToTask($project_uid, $activity_uid, $user_uid)
    {//POST /api/1.0/{workspace}/project/{prj_uid}/activity/{act_uid}/assignee
        //check to make sure the group isn't already assigned
        try{//GET/api/1.0/{workspace}/project/{prj_uid}/activity/{act_uid}/assignee/{aas_uid}

            $firstResult = $this->pmRestRequest("GET", "project/{$project_uid}/activity/{$activity_uid}/assignee/{$user_uid}");
        }
        catch(InvalidRequestException $e)
        {//group wasn't assigned
            $postParams = array(
                'aas_type'  => 'user',
                'aas_uid'    => $user_uid
            );
            $result = $this->pmRestRequest("POST", "project/{$project_uid}/activity/{$activity_uid}/assignee", $postParams);
            return $result;
        }

    }
    /*Function to obtain a new access token, using a refresh token. If the parameters are not specified
            then get them from the cookie if they exist. The new access token is set as a cookie available at $_COOKIE["access_token"]
            Parameters:
              clientId:     The cliend ID.
              clientSecret: The client secret code.
              refreshToken: The refreshToken from a previous call to /oauth2/token endpoint.
            Return value:
              Object returned by /oauth2/token endpoint, which either contains access token or error message.*/
    public function refreshToken($clientId=null, $clientSecret=null, $refreshToken=null)
    {
        $credentials = PMOauth::find($this->credentials_id)->first();
        $aVars = array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $credentials->client_id,
            'client_secret' => $credentials->client_secret,
            'refresh_token' => $this->refresh_token
        );

        $url = "{$this->pm_server}/{$this->pm_workspace}/oauth2/token";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aVars);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpStatus != 200) {
                print "Error in HTTP status code: $httpStatus\n";
                print $result->error;
            }
            elseif (isset($result->error)) {
                print "Error logging into $this->pm_server:\n" .
                    "Error:       {$result->error}\n" .
                    "Description: {$result->description}\n";
            }
            else {
//                //Save access token as a cookie that expires in 86400 seconds:
//                setcookie("access_token",  $result->access_token, time() + 86400);

                //If saving to a file:
                //file_put_contents("/secure/location/oauthAccess.json", json_encode($oToken));

                if ($httpStatus != 200) {
                    print "Error in HTTP status code: $httpStatus\n";
                }
                elseif (isset($result->error)) {
                    $server = $this->pm_server;
                    print "<pre>Error logging into $server:\n" .
                        "Error:       {$result->error}\n" .
                        "Description: {$result->error_description}\n</pre>";
                }
                else {
                    $encryptedAccessToken = \Crypt::encrypt($result->access_token);
                    $encryptedRefreshToken = \Crypt::encrypt($result->refresh_token);

                    $authorization = Authorization::firstOrNew(array('workspace' => $this->pm_workspace));
                    $authorization->workspace = $this->pm_workspace;
                    $authorization->encrypted_access_token = $encryptedAccessToken;
                    $authorization->encrypted_refresh_token = $encryptedRefreshToken;
                    $authorization->expires_in = $result->expires_in;
                    $authorization->token_type = $result->token_type;
                    $authorization->scope = $result->scope;
                    $authorization->save();

                    $_SESSION['pm_encrypted_access_token'] =$encryptedAccessToken;
                    $_SESSION['pm_encrypted_refresh_token'] =  $encryptedRefreshToken;
                }
            }

    }

    public function pmRestRequest($method, $endpoint, $aVars = null) {

        $apiServer = "{$this->pm_server}/api/1.0/{$this->pm_workspace}";
        //add beginning / to endpoint if it doesn't exist:
        if (!empty($endpoint) and $endpoint[0] != "/")
            $endpoint = "/" . $endpoint;


        $ch = curl_init($apiServer . $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->access_token));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $method = strtoupper($method);

        switch ($method) {
            case "GET":
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aVars));
                break;
            default:
                throw new Exception("Error: Invalid HTTP method '$method' $endpoint");
                return null;
        }

        $oRet = new \stdClass;
        $oRet->response = json_decode(curl_exec($ch));
        $info = curl_getinfo($ch);
        $oRet->status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $action=$method." ".$endpoint;
        if ($oRet->status == 401) { //access token has expired or is invalid
            //refresh token
            $this->refreshToken();
//            $baseUrl = Config::get('app.url', 'backend');
//            $parts =  parse_url($baseUrl);
//            $host = $parts['host'];
//            $pmServer = "http://{$host}:8080/sys{$workspace}/en/neoclassic/login/login";
//            header("Location: {$pmServer}"); //change to match your login method
//            die();
        }
        elseif ($oRet->status != 200 and $oRet->status != 201) { //if error
            if ($oRet->response and isset($oRet->response->error)) {
                $reason = $oRet->response->error->message;
                throw new InvalidRequestException($action, $reason, $oRet->response->error->code);
            }
            else {
                throw new InvalidRequestException($action, "unknown");
            }
        }
        else{
            return $oRet->response;
        }

    }

    public function loginUser($user_id, $user_password)
    {// Zero if a successful login or a non-zero error number if unsuccessful.
        $wsdl = "{$this->pm_server}/sys{$this->pm_workspace}/en/neoclassic/services/wsdl2";

        $trace = true;
        $exceptions = false;

        $xml_array['userid'] = $user_id;
        $xml_array['password'] = $user_password;

        try
        {
            $client = new \SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
            $response = $client->login($xml_array);
            if($response->status_code ==0)
            {
                return $response;
            }
            else{
                throw new InvalidRequestException("Logging in user", $response->message, $response->status_code);
            }
        }

        catch (Exception $e)
        {
            throw new InvalidRequestException("Logging in user", $e->getMessage(), $e->getCode());
        }

    }

    public function getRedirectUrl($user_role)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $workspace = $_SESSION['pm_workspace'];
        $pmServer = $_SESSION['pm_server'];
        switch($user_role)
        {
            case "PROCESSMAKER_ADMIN":
                return "{$pmServer}/sys{$workspace}/en/neoclassic/processes/main";
            case "PROCESSMAKER_OPERATOR":
            case "PROCESSMAKER_MANAGER":
            default:
                return "{$pmServer}/sys{$workspace}/en/neoclassic/cases/casesListExtJs";

        }
    }

    public function gradePostback($source_id, $url, $value, $oauth_consumer_key)
    {
        $xml_data ='<?xml version = "1.0" encoding = "UTF-8"?>'.
            '<imsx_POXEnvelopeRequest xmlns="http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">'.
                '<imsx_POXHeader>'.
                    '<imsx_POXRequestHeaderInfo>'.
                        '<imsx_version>V1.0</imsx_version>'.
                        '<imsx_messageIdentifier>999999123</imsx_messageIdentifier>'.
                    '</imsx_POXRequestHeaderInfo>'.
                '</imsx_POXHeader>'.
                '<imsx_POXBody>'.
                    '<replaceResultRequest>'.
                        '<resultRecord>'.
                            '<sourcedGUID>'.
                                '<sourcedId>'.$source_id.'</sourcedId>'.
                            '</sourcedGUID>'.
                            '<result>'.
                                '<resultScore>'.
                                    '<language>en</language>'.
                                    '<textString>'.$value.'</textString>'.
                                '</resultScore>'.
                            '</result>'.
                        '</resultRecord>'.
                    '</replaceResultRequest>'.
                '</imsx_POXBody>'.
            '</imsx_POXEnvelopeRequest>';

        //get the key and secret
        if (!isset($_SESSION)) {
            session_start();
        }

        $credentials = PMOauth::find($_SESSION['pm_credentials_id']);
        $secret = $credentials->secret;
        $method = 'POST';
        $content_type = 'application/xml';
        $sendOAuthBodyPOST = $this->sendOAuthBodyPOST($url, $oauth_consumer_key, $secret, $content_type, $xml_data);
        return $sendOAuthBodyPOST;
    }

    private function sendOAuthBodyPOST($url, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $body)
    {
        $hash = base64_encode(sha1($body, TRUE));

        $params = array('oauth_body_hash' => $hash);
        $test_token = '';
        $hmac_method = new OAuthSignatureMethodHMAC();
        $test_consumer = new OAuthConsumer($oauth_consumer_key, $oauth_consumer_secret, NULL);

        $acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $test_token, "POST", $url, $params);
        $acc_req->sign_request($hmac_method, $test_consumer, $test_token);

        $header = $acc_req->to_header();
        $header = $header . "\r\nContent-type: " . $content_type . "\r\n";

        $params = array('http' => array(
            'method' => 'POST',
            'content' => $body,
            'header' => $header
        ));
        try {
            $ctx = stream_context_create($params);
            $fp = @fopen($url, 'rb', false, $ctx);
        } catch (Exception $e) {
            $fp = false;
        }
        if ($fp) {
            $response = @stream_get_contents($fp);
        } else {  // Try CURL
            $headers = explode("\r\n",$header);
            $response = $this->sendXmlOverPost($url, $body, $headers);
        }

        if ($response === false) {
            throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        return $response;
    }
    function sendXmlOverPost($url, $xml, $header) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        // For xml, change the content-type.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned

        // Send to remote and return data to caller.
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }
}
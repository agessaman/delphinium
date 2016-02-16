<?php namespace Delphinium\Redwood;


use Config;
use Delphinium\Redwood\Exceptions\InvalidRequestException;

class RedwoodRoots
{

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
     * @param $group_title The title of the group (must be the assignment Id)
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

    public function createUser($first_name, $last_name, $canvas_user_id, $email, $pm_role)
    {//POST /api/1.0/{workspace}/user
//        $postParams = array(
//            'usr_username'    => $canvas_user_id,
//            'usr_firstname'   => $first_name,
//            'usr_lastname'  => $last_name,
//            'usr_email'=>$email,
//            'usr_due_date' =>'2020-12-31',
//            'usr_status' =>'ACTIVE',
//            'usr_role'=>$pm_role,
//            'usr_new_pass'=>,
//            'usr_cnf_pass'=>
//            );
//
//        if(!is_null($email)){
//            $postParams['usr_email'] = $email;
//        }
//        if(!is_null($pm_role)){
//            $postParams['usr_role'] = $pm_role;
//        }
//
//        $result = $this->pmRestRequest("POST", "user", $postParams);
//        return $result;
    }
    public function refreshToken()
    {

    }

    public function pmRestRequest($method, $endpoint, $aVars = null) {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $workspace = $_SESSION['pm_workspace'];
        $pmServer = $_SESSION['pm_server'];
        $accessToken = \Crypt::decrypt($_SESSION['pm_encrypted_access_token']);

        $apiServer = "{$pmServer}/api/1.0/{$workspace}";
        //add beginning / to endpoint if it doesn't exist:
        if (!empty($endpoint) and $endpoint[0] != "/")
            $endpoint = "/" . $endpoint;


        $ch = curl_init($apiServer . $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
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
            $baseUrl = Config::get('app.url', 'backend');
            $parts =  parse_url($baseUrl);
            $host = $parts['host'];
            $pmServer = "http://{$host}:8080/sys{$workspace}/en/neoclassic/login/login";
            header("Location: {$pmServer}"); //change to match your login method
            die();
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
}
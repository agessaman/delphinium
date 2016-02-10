<?php namespace Delphinium\Redwood;

class RedwoodRoots
{
    //todo: figure out logic for when the token needs to be refreshed
    function __construct()
    {

    }

    /*
     * @param $unique_department_name The name of the department. Must be unique
     * @param null $department_parent Optional: Parent department's unique ID
     * @param null $department_manager Optional: Department supervisor's unique ID
     * @param $department_status Optional: Department status, which can be "ACTIVE" or "INACTIVE". If not included, then "ACTIVE" by default.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function createDepartment($unique_department_name, $department_parent = null, $department_manager = null, $department_status = null)
    {//POST /api/1.0/{workspace}/department
        if(!isset($_SESSION))
        {
            session_start();
        }
        $workspace = $_SESSION['workspace'];
        $pmServer = $_SESSION['pm_server'];
        $url= "{$pmServer}/department";

        $postParams = array(
            'dep_title'    => $unique_department_name,
            'dep_parent'   => $department_parent,
            'dep_manager'  => $department_manager,
            'dep_status' => $department_status
        );

        try
        {
            $result = $this->postRequest($url,$postParams);
            return $result;
        }
        catch(Exception $e)
        {
            echo json_encode($e->getMessage());
        }
    }

    public function getUsers()
    {//GET /api/1.0/{workspace}/users?filter={filter}&start={start}&limit={limit}

        return $this->pmRestRequest("GET", "users");
    }
    private function postRequest($url, $params)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['encrypted_access_token']);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Length: ' . strlen($params),
            'Authorization: Bearer '.$token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    private function getRequest($url)
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $token = \Crypt::decrypt($_SESSION['encrypted_access_token']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $aUsers = json_decode($response);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);
        echo json_encode($info);

        $headers = $this->get_headers_from_curl_response($response);
        echo json_encode($headers);
        curl_close($ch);

        if ($statusCode == 401) { //if session has expired or bad login:
            header("Location: loginForm.php"); //change to match your login method
            die();
        }
        else
        if ($statusCode != 200) {
            if (isset ($aUsers) and isset($aUsers->error))
                echo "Error code: {$aUsers->error->code}\nMessage: {$aUsers->error->message}\n";
            else
                echo "Error: HTTP status code: $statusCode\n";
        }
        else {
            foreach ($aUsers as $oUser) {
                if ($oUser->usr_status == "ACTIVE") {
                    echo "{$oUser->usr_firstname} {$oUser->usr_lastname} ({$oUser->usr_username})\n";
                }
            }
        }
    }

    public function refreshToken()
    {

    }

    public function pmRestRequest($method, $endpoint, $aVars = null) {
        if(!isset($_SESSION))
        {
            session_start();
        }
        $workspace = $_SESSION['workspace'];
        $pmServer = $_SESSION['pm_server'];
        $accessToken = \Crypt::decrypt($_SESSION['encrypted_access_token']);
//echo $accessToken;
        if (empty($accessToken) and isset($_COOKIE['access_token']))
            $accessToken = $_COOKIE['access_token'];

        if (empty($accessToken)) { //if the access token has expired
            //To check if the PM login session has expired: !isset($_COOKIE['PHPSESSID'])
            header("Location: loginForm.php"); //change to match your login method
            die();
        }

        //add beginning / to endpoint if it doesn't exist:
        if (!empty($endpoint) and $endpoint[0] != "/")
            $endpoint = "/" . $endpoint;

        $ch = curl_init($pmServer . $endpoint);
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

        if ($oRet->status == 401) { //access token has expired or is invalid
            $baseUrl = Config::get('app.url', 'backend');
            $parts =  parse_url($baseUrl);
            $host = $parts['host'];
            $pmServer = "http://{$host}:8080/sys{$workspace}/en/neoclassic/login/login";
            header("Location: {$pmServer}"); //change to match your login method
            die();
        }
        elseif ($oRet->status == 302) { //if error
            echo "error is 302";
            echo json_encode($info);
        }
        elseif ($oRet->status != 200 and $oRet->status != 201) { //if error
            if ($oRet->response and isset($oRet->response->error)) {
                print "Error in $pmServer:\nCode: {$oRet->response->error->code}\n" .
                    "Message: {$oRet->response->error->message}\n";
            }
            else {
                print "Error: HTTP status code: $oRet->status\n";
            }
        }

        return $oRet;
    }
}
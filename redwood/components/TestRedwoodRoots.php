<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\RedwoodRoots;
use Delphinium\Redwood\Exceptions\InvalidRequestException;

class TestRedwoodRoots extends ComponentBase
{
    public $roots;

    public function componentDetails()
    {
        return [
            'name'        => 'TestRedwoodRoots Component',
            'description' => 'Playground for RedwoodRoots'
        ];
    }

    public function onRun()
    {
        $this->roots = new RedwoodRoots(1);
//        $this->test();
//        $this->testGetUsers();
//        $this->testCreateUser();
//        $this->testGetDepartments();
//        $this->testCreateDepartment();
//        $this->testGetGroup();
//        $this->testCreateGroup();
//        $this->testGetRoles();
//        $this->testPeerReviewWorkflow();
//        $this->testLoginUser();
        $this->testGradePostBack();

//        var_dump($_POST);
    }

    public function test()
    {
        echo json_encode($this->roots->getUsers());
    }

    public function testGetUsers()
    {
        $canvas_user_id = 1604486;
//        $res = $this->roots->getUsers();
        $res = $this->roots->getUsers($canvas_user_id);
        echo json_encode($res);
    }

    public function testCreateUser()
    {
        //we'd look through the roles and figure out which role we need
        //$roles = $this->testGetRoles();

        $first_name = "Tara";
        $last_name = "Jorgensen";
        $canvas_user_id = 10344545;
        $email = $canvas_user_id.'@uvlink.uvu.edu';//TODO: figure out a dynamic way to do the email
        $pm_role = "PROCESSMAKER_OPERATOR";
        $newUser = $this->roots->createUser($first_name, $last_name, $canvas_user_id, $email, $pm_role);
        echo json_encode($newUser);
    }

    public function testCreateDepartment()
    {
        $unique_department_name = "department"+time();
        $department_parent = "40673828156bbb673d391a0047310329";
        $department_manager = "00000000000000000000000000000001";
        $department_status="ACTIVE";
        $res = ($this->roots->createDepartment($unique_department_name,$department_parent,$department_manager,$department_status));
        echo json_encode($res);
    }

    public function testGetDepartments()
    {
        $pm_department_id = "40673828156bbb673d391a0047310329";
        $canvas_course_id = 343331;
        $res=($this->roots->getDepartments());
//        $res = ($this->roots->getDepartments($pm_department_id));//by department id
//        $res=($this->roots->getDepartments(null,$canvas_course_id));//by canvas course id
        echo json_encode($res);
    }

    public function testGetGroup()
    {
        $assignmentId = 1660429;
//        $assignmentId = 5432;
        $group = $this->roots->getGroups();//get all groups
        $group = $this->roots->getGroups($assignmentId);//get a specific assignment aka group
        echo json_encode($group);
    }

    public function testCreateGroup()
    {
        $assignmentId = 1660429;
        echo json_encode($this->roots->createGroup($assignmentId));
    }

    public function testGetRoles()
    {
        $res = $this->roots->getRoles();
        echo json_encode($res);
        return $res;
    }

    public function testPeerReviewWorkflow()
    {
        $courseId = 343331;
        $assignmentId = 1660429;
        $studentId = 1604486;

        //try to get a department with the given course ID. If not found, create one
        $depts = $this->roots->getDepartments(null,$courseId);
        $courseAsDepartment= null;
        if(count($depts)<1){
            $courseAsDepartment = ($this->roots->createDepartment($courseId));
        }
        else{
            $courseAsDepartment = $depts[0];
        }


        //try to get a group with the given assignment ID. If not found, create one$assignmentId = 1660429;
        $groups = $this->roots->getGroups($assignmentId);
        $assignmentAsGroup=null;
        if(count($groups)<1){
            $assignmentAsGroup =$this->roots->createGroup($assignmentId);
        }
        else{
            $assignmentAsGroup=$groups[0];
        }

        //try to get a student. If not found on PM, create a new one
        $users = $this->roots->getUsers($studentId);
        if(count($users)<1)
        {
            //$this->roots->createUser($first_name, $last_name, $canvaS_user_id);
        }
        echo json_encode($users);
    }

    public function testLoginUser()
    {
        $studentId = 123456;
        $users = $this->roots->getUsers($studentId);
        if(count($users)<1)
        {
            $user = $this->roots->createUser("Test", "User", $studentId, $studentId."@uvu.edu","PROCESSMAKER_OPERATOR");
            array_push($users,$user);
        }
        if(count($users)>0)
        {
            $response = $this->roots->loginUser($users[0]->usr_username,$users[0]->usr_username);
            var_dump($response);
        }
    }

    public function testGradePostBack()
    {//'ext_outcome_data_values_accepted' =>  'url,text'
//        $url = "https://uvu.instructure.com/api/lti/v1/tools/46776/grade_passback";//lis_outcome_service_url
//        //"https://uvu.instructure.com/api/lti/v1/tools/46776/ext_grade_passback";//ext_ims_lis_basic_outcome_url
//        $source_id = 614714;//lis_person_sourcedid
//        $value = 0.94;//between 0 and 1
//        $oauth_consumer_key = 'honey';//oauth_consumer_key
//        $secret = 'honey';
//        $oauth_signature = 'NrMvbW1cjuSaPGUAlx1phwxGWyQ';//oauth_signature
//        $oauth_signature_method="HMAC-SHA1'";//oauth_signature_method
//        $oauth_timestamp=1456173930;//oauth_timestamp
//        $oauth_nonce='yJheZTMRAujFThhy9rqEvL0SHhYUP010HXMfgoA5NE';//oauth_nonce
//        $oauth_version= 1.0;//oauth_version
//        $oauth_token = '??';


        $value = 0.5;
        $url = 'https://learn-lti.herokuapp.com/grade_passback/5607';
        $source_id = 'f3b73f9607';
        $oauth_consumer_key = '7257e50bf37455f398dddbeb40552d61';
        $oauth_signature_method = 'HMAC-SHA1';
        $oauth_timestamp = '1456175342';
        $oauth_nonce = 'uQiahSY8FSnGWsTdTD5fwnPlEdZReNHxL5NzSrntU';
        $oauth_version = '1.0';
        $oauth_signature = 'MurIq7dY4fGhJpkCKkxwTnerx8Q=';
        $realm = "http://uvu.instructure.com/";
//        $oauth_token =
        //build params
        $postParams = array(
            'oauth_consumer_key'    => $oauth_consumer_key,
            'oauth_token'   => $oauth_token,
            'oauth_signature_method'=>$oauth_signature_method,
            'oauth_signature' =>$oauth_signature,
            'oauth_timestamp' =>$oauth_timestamp,
            'oauth_nonce'=>$oauth_nonce,
            'oauth_version'=>$oauth_version,
            'realm'=>$realm
        );


        //then we need to sign this signature
        $adsfa = \OAuth::getRequestHeader ( 'POST',$url, [$postParams] );


        //sign body
//        $bodyHash = base64_encode(sha1($xml_data, TRUE)); // build oauth_body_hash
//        $consumer = new \OAuthConsumer($oauth_consumer_key, $secret);return;
//        $request = \OAuthRequest::from_consumer_and_token($consumer, '', 'POST', $endpoint, array('oauth_body_hash' => $bodyHash) );
//        $request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, '');
//        $header = $request->to_header() . "\r\nContent-Type: application/xml\r\n"; // add content type header



        var_dump($adsfa);return;



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
                                '<!-- Added element -->'.
                                '<resultScore>'.
                                    '<language>en</language>'.
                                    '<textString>'.$value.'</textString>'.
                                '</resultScore>'.
                            '</result>'.
                        '</resultRecord>'.
                    '</replaceResultRequest>'.
                '</imsx_POXBody>'.
            '</imsx_POXEnvelopeRequest>';



//        var_dump($this->arraytostr($postParams));
//        return;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', "Authorization: OAuth " . http_build_query($postParams)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = json_decode(curl_exec($ch));
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        var_dump($result);
        var_dump($status);
    }

    function arraytostr ($array=array()) {
        $string = '';
        $count = count($array);
        $i=0;
        foreach ($array as $key => $value) {
            $string .= "$key"."=";
            $string .= "'{$value}'";
            if($i<$count-1)
            {
                $string.=',';
            }
            $i++;
        }
        return $string;
    }

}
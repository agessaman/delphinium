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
        $url = "https://uvu.instructure.com/api/lti/v1/tools/46776/grade_passback";//lis_outcome_service_url
        //"https://uvu.instructure.com/api/lti/v1/tools/46776/ext_grade_passback";//ext_ims_lis_basic_outcome_url
        $source_id = 614714;//lis_person_sourcedid
        $value = 90;//out of 100 as set in custom_canvas_assignment_points_possible
        $oauth_consumer_key = 'honey';
        $secret = 'honey';
        $oauth_signature_method="HMAC-SHA1";
        $oauth_timestamp=1455838644;
        $oauth_nonce='VLe2r9KtU4ejAJwAWvzDF4Lvm1DACSsvDfq1UDFwU';
        $oauth_version= 1.0;
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
                                '<resultTotalScore>'.
                                    '<language>en</language>'.
                                    '<textString>'.$value.'</textString>'.
                                '</resultTotalScore>'.
                            '</result>'.
                        '</resultRecord>'.
                    '</replaceResultRequest>'.
                '</imsx_POXBody>'.
            '</imsx_POXEnvelopeRequest>';


        //sign body
        $bodyHash = base64_encode(sha1($xml_data, TRUE)); // build oauth_body_hash
        $consumer = new \OAuthConsumer($oauth_consumer_key, $secret);return;
        $request = \OAuthRequest::from_consumer_and_token($consumer, '', 'POST', $endpoint, array('oauth_body_hash' => $bodyHash) );
        $request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, '');
        $header = $request->to_header() . "\r\nContent-Type: application/xml\r\n"; // add content type header

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $token = "14~bW9RoI0juL1R0qxZfT8HHrcyVXO7DESCU1sT8r1aYZXwkHRWnAyLt5Q8GZ327JeO";
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', "Authorization: Bearer " . $token));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        var_dump($result);
    }


}
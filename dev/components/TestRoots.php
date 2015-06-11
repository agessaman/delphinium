<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\Models\Assignment;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\ModuleItemEnums\ModuleItemType;
use Delphinium\Core\Enums\ModuleItemEnums\CompletionRequirementType;
use Cms\Classes\ComponentBase;
use \DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;

use Delphinium\Core\Guzzle\GuzzleHelper;


class TestRoots extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Test Roots',
            'description' => 'This component will test the Roots API'
        ];
    }
    
    public function onRun()
    {  
//        $this->refreshCache();
//        $this->test();
        
//        Cache::flush();
//        $this->testBasicModulesRequest();
//        $this->testDeleteTag();
//        $this->testAddingUpdatingTags();
//        $this->testUpdatingModuleItem();
//        $this->testUpdatingModule();
        
//        $this->testDeletingModuleItem();
//        $this->testDeletingModule();   //need to double check this one
        
//        $this->testAddingModule();
//        $this->testAddingModuleItem();
//        
//        $this->testingGettingAssignments();
//        $this->testGettingSingleAssignment();
        
//        $this->testAssignmentGroups();
//        $this->testSingleAssignmentGroup();
//        
//        $this->testGettingSingleSubmissionSingleUserSingleAssignment();
//        $this->testGettingAllSubmissionForSingleAssignment();
//        $this->testGettingMultipleSubmissionsForSingleStudent();
//        $this->testGettingMultipleSubmissionsAllStudents();
//        $this->testGettingMultipleSubmissionsMultipleStudents();
//        $this->testGettingSubmissions();
//        $this->testFileUpload();
//        $this->testAddingAssignment();
//        $this->testStudentAnalyticsAssignmentDate();
        $this->testGetCourse();
    }
    
    private function testBasicModulesRequest()
    { 
        $moduleId = null;//457494;
        $moduleItemId = null;//2887055;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
        $freshData = true;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    
    private function testUpdatingModule()
    {   
        //380212
        $empty =  array();
        $module = new Module(null, null, null, null, 22);
        $req = new ModulesRequest(ActionType::PUT, 380206, null,  
            false, false, $module, null , false);
        $roots = new Roots();
        $res = $roots->modules($req);
        
//        $name = "Updated from backend";
//        
//        $format = DateTime::ISO8601;
//        $date = new DateTime("now");
//        $date->add(new DateInterval('P1D'));
//        $unlock_at = $date;
//        $prerequisite_module_ids =array("380199","380201");
//        $published = true;
//        $position = 4;
//        
//        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, $position);
//        
//        
//        $moduleId = 457494;
//        $moduleItemId = null;
//        $includeContentItems = false;
//        $includeContentDetails = false;
//        $moduleItem = null;
//        $freshData = false;
//        
//        //update a module (changing title and published to false)
//        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
//            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
//        
//        $roots = new Roots();
//        $res = $roots->modules($req);
    }
    
    private function testUpdatingModuleItem()
    {
        //added
        $tags = null;//array('New Tag', 'Another New Tag');
        $title = "New Title from back end";
        $modItemType = null;// Module type CANNOT be updated
        $content_id = 2078183;
        $completion_requirement_min_score = null;//7;
        $completion_requirement_type = null;//CompletionRequirementType::MUST_SUBMIT;
        $page_url = null;//"http://www.gmail.com";
        $published = true;
        $position = 1;//2;
        
        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, $completion_requirement_type, 
                $completion_requirement_min_score, $published, $position, $tags);
        //end added
        
        $moduleId = 457097;
        $moduleItemId = 2885671;
        $includeContentItems = false;
        $includeContentDetails = false;
        $module = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    
    private function testDeletingModuleItem()
    {
        $moduleId = 457097;
        $moduleItemId = 2887052;
        $includeContentItems = false;
        $includeContentDetails = false;
        $module = null;
        $moduleItem = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::DELETE, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testDeletingModule()
    {
        $moduleId = 457079;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $module = null;
        $moduleItem = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::DELETE, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
//        \Cache::flush();
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    private function testAddingModule()
    {
        $name = "Module from backend";
        
        $format = DateTime::ISO8601;
        $date = new DateTime("now");
//        $date->add(new DateInterval('P1D'));
        $unlock_at = $date;
        $prerequisite_module_ids =array("380199","380201");
        $published = true;
        $position = 1;
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, $position);
        $moduleId = null;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $moduleItem = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::POST, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testAddingModuleItem()
    {
        $tags = array('Brand', 'New');
        $title = "Module Item created from the backend";
        $modItemType = ModuleItemType::FILE;
        $content_id = 49051689;
        $completion_requirement_min_score = 6;
        $page_url = "http://www.google.com";
        $published = true;
        $position = 1;
        
        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, CompletionRequirementType::MUST_SUBMIT, 
                $completion_requirement_min_score, $published, $position, $tags);
                
        $moduleId = 457494;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $freshData = false;
        $module = null;
        
        $req = new ModulesRequest(ActionType::POST, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails,  $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testingGettingAssignments()
    {
        $req = new AssignmentsRequest(ActionType::GET);
        
        $roots = new Roots();
        $res = $roots->assignments($req);
        echo json_encode($res);
    }
    
    private function testGettingSingleAssignment()
    {
        $assignment_id = 1660430;
        $freshData = false;
        $req = new AssignmentsRequest(ActionType::GET, $assignment_id, $freshData);
        
        $roots = new Roots();
        $res = $roots->assignments($req);
        echo json_encode($res);
    }
    
    private function testAssignmentGroups()
    {
        $include_assignments = true;
        $fresh_data = true;
        $assignmentGpId = null;
        $req = new AssignmentGroupsRequest(ActionType::GET, $include_assignments, $assignmentGpId, $fresh_data);
        
        $roots = new Roots();
        $res = $roots->assignmentGroups($req);
        echo json_encode($res);   
    }
    
    private function testSingleAssignmentGroup()
    {
        $assignment_group_id = 378245;
        $req = new AssignmentGroupsRequest(ActionType::GET, true, $assignment_group_id);
        
        $roots = new Roots();
        $res = $roots->assignmentGroups($req);
        echo json_encode($res);
    }
    
    
    private function testGettingSingleSubmissionSingleUserSingleAssignment()
    {
        $studentIds = array(1489289);
        $assignmentIds = array(1660419);
        $multipleStudents = false;
        $multipleAssignments = false;
        $allStudents = false;
        $allAssignments = false;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo json_encode($res);
    }
    
    private function testGettingAllSubmissionForSingleAssignment()
    {
        $studentIds = array(10733259,10733259);
        $assignmentIds = array(1660406);//array(1660419);
        $multipleStudents = true;
        $multipleAssignments = false;
        $allStudents = true;
        $allAssignments = false;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo json_encode($res);
    }
    
    private function testGettingMultipleSubmissionsForSingleStudent()
    {
        $studentIds = array(10733259);
        $assignmentIds = array(1660419, 1660406, 1660412);
        $multipleStudents = false;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = false;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo json_encode($res);
    }
    
    private function testGettingMultipleSubmissionsAllStudents()
    {
        $studentIds = null;
        $assignmentIds = array(1660419, 1660406, 1660412);
        $multipleStudents = true;
        $multipleAssignments = true;
        $allStudents = true;
        $allAssignments = false;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo json_encode($res);
    }
        
    private function testGettingMultipleSubmissionsMultipleStudents()
    {//This throws an error because I'm not authorized to retrieve submissions in behalf of other students
        $studentIds = array(10733259,10733259);
        $assignmentIds = array(1660419, 1660406, 1660412);
        $multipleStudents = true;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = false;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo json_encode($res);
    }
    
    private function refreshCache()
    {
        $moduleId = null;
        $includeContentDetails = true;
        $includeContentItems = true;
        $moduleItemId = null;
        $refreshData = true;
        
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, $includeContentDetails, null, 
                null, $refreshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    
    public function test()
    {
        $file = "file.txt";
        $url = "https://uvu.instructure.com/api/v1/courses/343331/files?name=file.txt&size=19&content_type=text/plain&on_duplicate=rename&access_token=14~VsT5x3fmVUN5fmVzHhDvfTLxurX2RYlIBcVxzgSs4SKBUTMcObOGgGW8iROy93M1";
        $client = new Client();
        $response = $client->post($url);
        
        
        echo json_encode($response->getBody());
        return $response;
        
//        $upload_url ="https://instructure-uploads-2.s3.amazonaws.com/";
//        $arrParams=["AWSAccessKeyId"=>"AKIAJFNFXH2V2O7RPCAA",
//                        "Filename"=>"",
//                        "key"=>"account_140000000000016/attachments/59365510/file.txt",
//                        "acl"=>"private",
//                        "Policy"=>"eyJleHBpcmF0aW9uIjoiMjAxNS0wNi0xMFQyMjo1MjozMVoiLCJjb25kaXRpb25zIjpbeyJidWNrZXQiOiJpbnN0cnVjdHVyZS11cGxvYWRzLTIifSx7ImtleSI6ImFjY291bnRfMTQwMDAwMDAwMDAwMDE2XC9hdHRhY2htZW50c1wvNTkzNjU1MTBcL2ZpbGUudHh0In0seyJhY2wiOiJwcml2YXRlIn0sWyJzdGFydHMtd2l0aCIsIiRGaWxlbmFtZSIsIiJdLFsiY29udGVudC1sZW5ndGgtcmFuZ2UiLDEsMTA3Mzc0MTgyNDBdLHsic3VjY2Vzc19hY3Rpb25fcmVkaXJlY3QiOiJodHRwczpcL1wvdXZ1Lmluc3RydWN0dXJlLmNvbVwvYXBpXC92MVwvZmlsZXNcLzU5MzY1NTEwXC9jcmVhdGVfc3VjY2Vzcz9vbl9kdXBsaWNhdGU9cmVuYW1lXHUwMDI2dXVpZD1qbThsQVVuMDJwVXhRUVVweHVnWlhxVnMzRmp4QXhFSlY2WXBYQ2tyIn0seyJjb250ZW50LXR5cGUiOiJ0ZXh0XC9wbGFpbiJ9XX0=",
//                        "Signature"=>"B89+tKziB4y6+IdnCG7EiYa4+aY=",
//                        "success_action_redirect"=>"https://uvu.instructure.com/api/v1/files/59365510/create_success?on_duplicate=rename\u0026uuid=jm8lAUn02pUxQQUpxugZXqVs3FjxAxEJV6YpXCkr",
//                        "content-type"=>"text/plain"];

        
        
        
/*
        // Create the request.
        $request = $client->createRequest("POST", $upload_url);

        // Set the POST information.
        $postBody = $request->getBody();
        foreach($arrParams as $key=>$value)
        {
            $postBody->setField($key, $value);
        }
        $fileName = "/Users/damaris/Desktop/".$file;
        $postBody->addFile(new PostFile('file', fopen($fileName, 'r', 1)));

        echo json_encode($request);
        // Send the request and get the response.
        $result = $client->send($request);
        echo json_encode($result);
        return $result;
        
        
        
        $client = new Client();
        $result = $client->post($upload_url, [
            'body' => [
                $arrParams,
                'file'   => fopen('/Users/damaris/Desktop/'.$file, 'r')
            ]
        ]);
        
        */
//        $upload_url="https://instructure-uploads-2.s3.amazonaws.com/";
//        $arrParams = ["AWSAccessKeyId"=>"AKIAJFNFXH2V2O7RPCAA",
//                    "Filename"=>"",
//                    "key"=>"account_140000000000016/attachments/59365336/file.txt",
//                    "acl"=>"private",
//                    "Policy"=>"eyJleHBpcmF0aW9uIjoiMjAxNS0wNi0xMFQyMjo0MjowNFoiLCJjb25kaXRpb25zIjpbeyJidWNrZXQiOiJpbnN0cnVjdHVyZS11cGxvYWRzLTIifSx7ImtleSI6ImFjY291bnRfMTQwMDAwMDAwMDAwMDE2XC9hdHRhY2htZW50c1wvNTkzNjUzMzZcL2ZpbGUudHh0In0seyJhY2wiOiJwcml2YXRlIn0sWyJzdGFydHMtd2l0aCIsIiRGaWxlbmFtZSIsIiJdLFsiY29udGVudC1sZW5ndGgtcmFuZ2UiLDEsMTA3Mzc0MTgyNDBdLHsic3VjY2Vzc19hY3Rpb25fcmVkaXJlY3QiOiJodHRwczpcL1wvdXZ1Lmluc3RydWN0dXJlLmNvbVwvYXBpXC92MVwvZmlsZXNcLzU5MzY1MzM2XC9jcmVhdGVfc3VjY2Vzcz9vbl9kdXBsaWNhdGU9cmVuYW1lXHUwMDI2dXVpZD1GaW5WS0tjM1NLT1huNkpMZlc3VXp6Y3ptem1BaGpVZlFwajBrbndXIn0seyJjb250ZW50LXR5cGUiOiJ0ZXh0XC9wbGFpbiJ9XX0=",
//                    "Signature"=>"O315NNvI3/TzZNPWXoyIheGhxg4=",
//                    "success_action_redirect"=>"https://uvu.instructure.com/api/v1/files/59365336/create_success?on_duplicate=rename\u0026uuid=FinVKKc3SKOXn6JLfW7UzzczmzmAhjUfQpj0knwW",
//                    "content-type"=>"text/plain"];
        
        
//        $date = '2015-06-02T22:33:14.798Z';
//        echo strtotime($date)."--";
//        $due_at = new DateTime("2010-12-07T23:00:00.000Z");//DateTime::createFromFormat(DateTime::ISO8601, $date);
//        echo json_encode($due_at);
//        
//        
//        $format = DateTime::ISO8601;
//        $date = new DateTime("now");
////        $date->add(new DateInterval('P1D'));
//        
//        echo json_encode($date); 
        
        //"380199",
//        $arr = array("380200","380201","380202","380203","380204","380205","380206","380207","380208","380209","380210","380211",
//            "380212","380213","380214","380215","380216","380217","380218","380219","380220","380221","456852","456876","456877","456878");
//        
//        $dbHelper = new DbHelper();
//        $dbHelper->qualityAssuranceModules(343331, $arr);
        
        
        
//        $moduleId = null;
//        $moduleItemId = null;
//        $includeContentDetails = true;
//        $includeContentItems = true;
//        $module = null;
//        $moduleItem = null;
//        $freshData = false;
//                
//        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
//                $includeContentDetails, $module, $moduleItem , $freshData);
//        
//        $roots = new Roots();
//        $moduleData = $roots->modules($req);
//        echo $moduleData;
    }
    
    function testAddingUpdatingTags()
    {
        //To add/update tags the bare minimum that is needed is the content id and the tags.
        //A moduleItem can be updated on Canvas and have tags added to it in the same request IF the module_item_id is provided
        
        $tags = array('New Tag', 'Another New Tag');
        $title = null;
        $modItemType = null;
        $content_id = 49051678;
        $completion_requirement_min_score = null;
        $completion_requirement_type = null;
        $page_url = null;
        $published = true;
        $position = null;
        
        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, $completion_requirement_type, 
                $completion_requirement_min_score, $published, $position, $tags);
        //end added
        
        $moduleId = null;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $module = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return $res;
    }
    
    public function testDeleteTag()
    {
        $tags = array('New Tag', 'Another New Tag');
        $title = null;
        $modItemType = null;
        $content_id = 49051678;
        $completion_requirement_min_score = null;
        $completion_requirement_type = null;
        $page_url = null;
        $published = true;
        $position = null;
        
        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, $completion_requirement_type, 
                $completion_requirement_min_score, $published, $position, $tags);
        //end added
        
        $moduleId = null;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $module = null;
        $freshData = false;
        
        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return $res;
    }
    
    public function testFileUpload()
    {
//        /api/v1/courses/:course_id/files
        
    }
    
    public function testAddingAssignment()
    {
        $date = new DateTime("now");
        $assignment = new Assignment();
        $assignment->name = "my new name";
        $assignment->description = "This assignment was created from backend";
        $assignment->points_possible = 30;
        $assignment->due_at = $date;
        
        $req = new AssignmentsRequest(ActionType::POST, null, null, $assignment);
        
        $roots = new Roots();
        $res = $roots->assignments($req);
        echo json_encode($res);
    }
    
    public function testStudentAnalyticsAssignmentDate()
    {
        $roots = new Roots();
        $res = $roots->getAnalyticsStudentAssignmentData();
        echo json_encode($res);
    }
    
    public function testGetCourse()
    {
        $roots = new Roots();
        $res = $roots->getCourse();
        echo json_encode($res);
    }
}


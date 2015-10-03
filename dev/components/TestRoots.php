<?php namespace Delphinium\Dev\Components;

use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\Models\ModuleItem as DbModuleItem;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Utils;
use Delphinium\Roots\Requestobjects\SubmissionsRequest;
use Delphinium\Roots\Requestobjects\ModulesRequest;
use Delphinium\Roots\Requestobjects\AssignmentsRequest;
use Delphinium\Roots\Requestobjects\QuizRequest;
use Delphinium\Roots\Requestobjects\AssignmentGroupsRequest;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Enums\ModuleItemType;
use Delphinium\Roots\Enums\CompletionRequirementType;
use Delphinium\Roots\DB\DbHelper;
use Cms\Classes\ComponentBase;
use \DateTime;
use \DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;
use Delphinium\Iris\Components\Iris;
use Cms\Classes\ComponentManager;
use \Delphinium\Blade\Classes\Rules\RuleBuilder;
use \Delphinium\Blade\Classes\Rules\RuleGroup;

use Delphinium\Roots\Guzzle\GuzzleHelper;


class TestRoots extends ComponentBase
{
    public $roots;
    public function componentDetails()
    {
        return [
            'name'        => 'Test Roots',
            'description' => 'This component will test the Roots API'
        ];
    }
    
    public function onRun()
    {  
        $this->roots = new Roots();
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
        $this->testGettingAllSubmissionForSingleAssignment();
//        $this->testGettingMultipleSubmissionsForSingleStudent();
//        $this->testGettingMultipleSubmissionsAllStudents();
//        $this->testGettingAllSubmissionsAllStudents();
//        $this->testGettingMultipleSubmissionsMultipleStudents();
//        $this->testGettingSubmissions();
//        $this->testFileUpload();
//        $this->testAddingAssignment();
//        $this->testStudentAnalyticsAssignmentData();
//        $this->testGetCourse();
//        $this->testGetAccount();
//        $this->testGetEnrollments();
//        $this->testGetQuiz();
//        $this->testGetQuizQuestions();
//        $this->testGetAllQuizzes();
//        $this->testGetPages();
        
    }
    
    private function testBasicModulesRequest()
    { 
        $moduleId = null;//380200;
        $moduleItemId = null;//2368085;
        $includeContentDetails = true;
        $includeContentItems = true;
        $module = null;
        $moduleItem = null;
        $freshData = true;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $res = $this->roots->modules($req);
        echo json_encode($res);
    }
    
    
    private function testUpdatingModule()
    {   
        //380212
        $empty =  array();
        $module = new Module(null, null, null, null, 22);
        $req = new ModulesRequest(ActionType::PUT, 380206, null,  
            false, false, $module, null , false);
        
        $res = $this->roots->modules($req);
        
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
//        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->modules($req);
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
        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->modules($req);
        echo json_encode($res);
    }
    
    private function testingGettingAssignments()
    {
        $req = new AssignmentsRequest(ActionType::GET, null, false, null, true);
//        $req = new AssignmentsRequest(ActionType::GET);
        
        $res = $this->roots->assignments($req);
        echo json_encode($res);
    }
    
    private function testGettingSingleAssignment()
    {
        $assignment_id = 1660430;
        $freshData = false;
        $includeTags = true;
        $req = new AssignmentsRequest(ActionType::GET, $assignment_id, $freshData, null, $includeTags);
        $res = $this->roots->assignments($req);
        echo json_encode($res);
    }
    
    private function testAssignmentGroups()
    {
        $include_assignments = true;
        $fresh_data = true;
        $assignmentGpId = null;
        $req = new AssignmentGroupsRequest(ActionType::GET, $include_assignments, $assignmentGpId, $fresh_data);
        
        $res = $this->roots->assignmentGroups($req);
        echo json_encode($res);   
    }
    
    private function testSingleAssignmentGroup()
    {
        $assignment_group_id = 378245;
        $req = new AssignmentGroupsRequest(ActionType::GET, true, $assignment_group_id);
        
        $res = $this->roots->assignmentGroups($req);
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
        
        $res = $this->roots->submissions($req);
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
        
        $res = $this->roots->submissions($req);
        echo json_encode($res);
    }
    
    private function testGettingMultipleSubmissionsForSingleStudent()
    {
        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $studentId = $_SESSION['userID'];
        
        $studentIds = array($studentId);
        $assignmentIds = array();
        $multipleStudents = false;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = true;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $res = $this->roots->submissions($req);
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
        
        $res = $this->roots->submissions($req);
        echo json_encode($res);
    }
       
    private function testGettingAllSubmissionsAllStudents()
    {
        $studentIds = null;
        $assignmentIds = array();
        $multipleStudents = true;
        $multipleAssignments = true;
        $allStudents = true;
        $allAssignments = true;
        $includeTags = true;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments, $includeTags);
        
        $res = $this->roots->submissions($req);
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
        
        $res = $this->roots->submissions($req);
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
        
        $res = $this->roots->modules($req);
        echo json_encode($res);
    }
    
    public function convertDatesUTCLocal()
    {
        $utcTime = Utils::convertLocalDateTimeToUTC(new DateTime('now'));
        echo "UTC:".json_encode($utcTime);
        
        $localTime = Utils::convertUTCDateTimetoLocal($utcTime);
        echo "MOUNTAIN".json_encode($localTime);
        
    }
    
    public function test()
    {
        
        
//        $this->convertDatesUTCLocal();
//        $now = new DateTime(date("Y-m-d"));
//        echo json_encode($now);
//        
//        
//        
//        $rb = new RuleBuilder;
//
//        $bonus_90 = $rb->create('current_user_submissions', 'submission',
//        $rb['submission']['score']->greaterThan($rb['score_threshold']),
//        [
//            $rb['(bonus)']->assign($rb['(bonus)']->add($rb['points']))
//        ]);
//        
//        $rb['(bonus)'] = 0;
//        $rb['submission']['score'] = 0;
//        $rb['score_threshold'] = 0;
//        $rb['point'] = 0;
//
//        $rg = new RuleGroup('submissionstest');
//        $rg->add($bonus_90);
//        $rg->saveRules();
        
//        $manager = ComponentManager::instance();
//       echo json_encode($manager->listComponents());
        
        

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
        
        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->modules($req);
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
        
        $res = $this->roots->assignments($req);
        echo json_encode($res);
    }
    
    public function testStudentAnalyticsAssignmentData()
    {
        $res = $this->roots->getAnalyticsStudentAssignmentData(false);
        echo json_encode($res);
    }
    
    public function testGetCourse()
    {
        $res = $this->roots->getCourse();
        echo json_encode($res);
    }
    
    public function testGetAccount()
    {
        $accountId = 16;
        $res = $this->roots->getAccount($accountId);
        echo json_encode($res);
    }

    public function testGetEnrollments()
    {
        $res = $this->roots->getUserEnrollments();
        echo json_encode($res);
    }
    
    public function testGetAllQuizzes()
    {
//        $req = new QuizRequest(ActionType::GET, null, $fresh_data = false, true);
        $req = new QuizRequest(ActionType::GET, null, $fresh_data = true, true);
        echo json_encode($this->roots->quizzes($req));
    }
    public function testGetQuiz()
    {   
        $req = new QuizRequest(ActionType::GET, 464878, $fresh_data = true, true);
        $result = $this->roots->quizzes($req);
        echo json_encode($result);
    }
    public function testGetPages()
    {
        echo json_encode($this->roots->getPages());
    }
    
    public function testGetQuizQuestions()
    {
        $req = new QuizRequest(ActionType::GET, 464878, false, true);
        $result = $this->roots->quizzes($req);
        echo json_encode($result);
    }
    private function convertToUTC()
    {
        $date = new DateTime("now", new \DateTimeZone('America/Denver'));
        echo json_encode($date);
        
        $UTC = new DateTimeZone("UTC");
        $utc_date = $date->setTimezone( $UTC );
        echo json_encode($utc_date);
    }
    
    
    
}



<?php namespace Delphinium\Iris\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Core\Models\Page;
use Delphinium\Core\Models\Discussion;
use Delphinium\Core\Models\File;
use Delphinium\Core\Models\Quiz;
use Delphinium\Core\Models\Assignment;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Roots;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Iris\Components\Angular;
use \DateTime;


class RestApi extends Controller 
{
    public function getFreshData()
    {
        $ang = new Angular();
        $modules = $ang->getModules(true);
        return $modules;
    }
    
    public function getAvailableTags()
    {
        $roots = new Roots();
        return $roots->getAvailableTags();
        
    }

    public function getModuleStates()
    {
        $moduleId = null;
        $moduleItemId = null;
        $includeContentDetails = false;
        $includeContentItems = false;
        $module = null;
        $moduleItem = null;
        $freshData = true;
                
        $req = new ModulesRequest(ActionType::GET, $moduleId, $moduleItemId, $includeContentItems, 
                $includeContentDetails, $module, $moduleItem , $freshData) ;
        
        $roots = new Roots();
        $res = $roots->getModuleStates($req);
        
        return $res;
    }

    public function getStudentSubmissions()
    {
        $studentId = \Input::get('studentId');
        
        $studentIds = array($studentId);
        $assignmentIds = array();//if we leave this param empty it will return all of the available submissions 
        //(see https://canvas.instructure.com/doc/api/submissions.html#method.submissions_api.for_students)
        $multipleStudents = false;
        $multipleAssignments = true;
        $allStudents = false;
        $allAssignments = true;
        
        //can have the student Id param null if multipleUsers is set to false (we'll only get the current user's submissions)
        $req = new SubmissionsRequest(ActionType::GET, $studentIds, $allStudents, 
                $assignmentIds, $allAssignments, $multipleStudents, $multipleAssignments);
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        return $res;
    }
    
    public function getModuleItemTypes()
    {
        $roots = new Roots();
        $arr = $roots->getModuleItemTypes();
        $result = array();
        $i=0;
        foreach($arr as $key => $type)
        {   
            $item = new \stdClass();
            
            $item->id = $i;
            $item->value=$type;
            $result[] = $item;
            
            $i++;
        }
        return json_encode($result);
    }
    
    public function getAssignmentGroups()
    {
        $req = new AssignmentGroupsRequest(ActionType::GET, false, null, true);
        
        $roots = new Roots();
        return $res = $roots->assignmentGroups($req);
    }
    
    public function getPageEditingRoles()
    {
        $roots = new Roots();
        $arr = $roots->getPageEditingRoles();
        $result = array();
        $i=0;
        foreach($arr as $type)
        {   
            $item = new \stdClass();
            
            $item->id = $i;
            $item->value=$type;
            $result[] = $item;
            
            $i++;
        }
        return json_encode($result);
    }
    
    public function moveItemToTop()
    {
        $parent = json_decode(\Input::get('parent'), true);
        $threeDArrayWithoutParent = json_decode(\Input::get('modulesArray'), true);
        $iris = new IrisClass();
        $result = $iris->makeItemParent($threeDArrayWithoutParent,($parent));            
        return $result;
    }
    
    public function saveModules()
    {
        $courseId = \Input::get('courseId');
        $modulesArray = \Input::get('modulesArray');
        $updateLms = \Input::get('updateLms');
        
        $decoded = json_decode($modulesArray);
        
        $flat = $this->flatten($decoded, $courseId);

        $roots = new \Delphinium\Core\Roots();
        $mods = $roots->updateModuleOrder($flat, $updateLms);
      
        
//        echo " The order is: ---- ".json_encode($flat)." ---";
        $iris = new IrisClass();
        $result = $iris->buildTree($mods);
        return $result;
    }

    private function flatten(array $array, $courseId) 
    {
        //we will pass this value by reference
        $flatArray = array();
        $order = 0;

        $iris = new \Delphinium\Iris\Classes\Iris();
        $iris->recursive($courseId, $array, $flatArray);
        return $flatArray;
    }

    public function addTags()
    {
        $content_id = \Input::get('contentId');
        $tags = \Input::get('tags');
        
//        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, $external_url, $completion_requirement_type, 
//                $completion_requirement_min_score, $published, $position, $tags);
        $moduleItem = new ModuleItem(null, null, intval($content_id), null, null, null, 
                null, null, null, json_decode($tags, true));
        //end added
        
//        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
//            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        $req = new ModulesRequest(ActionType::PUT, null, null,  
            null, null, null, $moduleItem , null);
        
        $roots = new Roots();
        return $roots->modules($req);
    }

    public function deleteTag()
    {   
        $content_id = \Input::get('contentId');
        $tags = \Input::get('tags');
        
//        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, $external_url, $completion_requirement_type, 
//                $completion_requirement_min_score, $published, $position, $tags);
        $moduleItem = new ModuleItem(null, null, intval($content_id), null, null, null, 
                null, null, null, json_decode($tags, true));
        //end added
        
//        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
//            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        $req = new ModulesRequest(ActionType::PUT, null, null,  
            null, null, null, $moduleItem , null);
        
        $roots = new Roots();
        return $roots->modules($req);
    }

    public function toggleModulePublishedState()
    {
        $module_id = \Input::get('module_id');
        $published = \Input::get('published');
        
        $publishedState = $published===1?true:false;
        
        //($name = null,  DateTime $utc_unlock_at = null, array $prerequisite_module_ids = null, $published = null, $position =1)
        $module = new Module(null, null, null, $publishedState, null);
        $req = new ModulesRequest(ActionType::PUT, $module_id, null,  
            false, false, $module, null , false);
        $roots = new Roots();
        $roots->modules($req);
    }
    
    public function toggleModuleItemPublishedState()
    {
        $module_id = \Input::get('module_id');
        $module_item_id = \Input::get('module_item_id');
        $published = \Input::get('published');
        
        $publishedState = $published===1?true:false;
        
        $moduleItem = new ModuleItem(null, null, null, null, null, null, 
                null, $publishedState, null, null);
        
        $req = new ModulesRequest(ActionType::PUT, $module_id, $module_item_id,  
            false, false, null, $moduleItem , false);
        
        $roots = new Roots();
        $roots->modules($req);
    
    }
    
    public function updateModule()
    {
        $name = "Updated from backend";
        
        $format = DateTime::ISO8601;
        $date = new DateTime("now");
        $date->add(new DateInterval('P1D'));
        $unlock_at = $date;
        $prerequisite_module_ids =array("380199","380201");
        $published = true;
        $position = 4;
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, $position);
        
        
        $moduleId = 457494;
        $moduleItemId = null;
        $includeContentItems = false;
        $includeContentDetails = false;
        $moduleItem = null;
        $freshData = false;
        
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    public function updateModuleItem()
    {
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
    
    public function deleteModule()
    {
        $moduleId = \Input::get('module_id');
        
        //delete module
        $req = new ModulesRequest(ActionType::DELETE, $moduleId, null,false, false, null, null , false);
        
        $roots = new Roots();
        $roots->modules($req);
        
        //return all modules again
        $newReq = new ModulesRequest(ActionType::GET, null, null, true, true, null, null , false) ;
        $res = $roots->modules($newReq);
        return $res;
    }
    
    public function deleteModuleItem()
    {
        $moduleId = \Input::get('module_id');
        $moduleItemId = \Input::get('module_item_id');
        
        $req = new ModulesRequest(ActionType::DELETE, $moduleId, $moduleItemId, false, false, null, null , false);
        
        $roots = new Roots();
        $roots->modules($req);
        
        //return all modules again
        $newReq = new ModulesRequest(ActionType::GET, null, null, true, true, null, null , false) ;
        $res = $roots->modules($newReq);
        return $res;
    }
   
    public function addNewPage()
    {
        $title = \Input::get('title');
        $body = \Input::get('body');
        $pageEditingRole = \Input::get('pageEditingRole');
        $notifyOfUpdate = (\Input::get('notifyOfUpdate'))?true:false;
        $published = true;
        
        $page = new Page($title, $body, $pageEditingRole,  $notifyOfUpdate, $published);
        $roots = new Roots();
        return $roots->addPage($page);
    }
    
    public function addNewDiscussionTopic()
    {
        $title = \Input::get('title');
        $message = \Input::get('message');
        $threaded = \Input::get('threaded');
        $delayed_post_at = \Input::get('delayed_post_at');
        $lock_at = \Input::get('lock_at');
        $podcast_enabled = \Input::get('podcast_enabled');
        $require_initial_post = \Input::get('require_initial_post');
        $podcast_has_student_posts = \Input::get('podcast_has_student_posts');
        $is_announcement= \Input::get('is_announcement');
        $published = true;
        
        $discussion = new Discussion($title, $message, $threaded,  $delayed_post_at, $lock_at, $podcast_enabled,
                $podcast_has_student_posts, $require_initial_post,$is_announcement, $published, null);
        $roots = new Roots();
        return $roots->addDiscussion($discussion);
    }
    
    public function addNewAssignment()//date must be in UTC
    {
        $name = \Input::get('name');
        $date = \Input::get('due_at');
        $due_at= new DateTime($date);
        $points_possible = \Input::get('points');
            
        $assignment = new Assignment();
        $assignment->name = $name;
        $assignment->points_possible = $points_possible;
        $assignment->due_at = $due_at;
        
        $req = new AssignmentsRequest(ActionType::POST, null, null, $assignment);
        
        $roots = new Roots();
        $res = $roots->assignments($req);
        return json_encode($res);
    }
    
    public function addNewQuiz()//date must be in UTC
    {
        $title = \Input::get('title');
        $date = \Input::get('due_at');
        $due_at= new DateTime($date);
        $published = true;
        
        $quiz = new Quiz($title, $due_at, $published);
        
        $roots = new Roots();
        $res = $roots->addQuiz($quiz);
        return json_encode($res);
    }
    
    public function addNewExternalTool()
    {
        $name = \Input::get('name');
        $url = \Input::get('url');
        
        $externalTool = new \stdClass();
        $externalTool->name = $name;
        $externalTool->url = json_encode($url);
        
        $roots = new Roots();
        return $roots->addExternalTool($externalTool);
    }
    
    public function uploadFile()
    {
        $name = \Input::get('name');
        $size = \Input::get('size');
        $content_type = \Input::get('content_type');
        $on_duplicate = "rename";
        
        $file = new File($name, $size, $content_type, $on_duplicate);
        $roots = new Roots();
        return $roots->uploadFile($file);
    }
    
    public function uploadFileStepTwo()
    {
        $params = \Input::get('params');
        $file = \Input::get('file');
        $upload_url = \Input::get('upload_url');
        $roots = new Roots();
        return $roots->uploadFileStepTwo($params, $file, $upload_url);
    }
    
    public function uploadFileStepThree()
    {
        $location = \Input::get('location');
        $roots = new Roots();
        return $roots->uploadFileStepThree($location);
    }
}

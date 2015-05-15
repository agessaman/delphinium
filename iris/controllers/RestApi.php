<?php namespace Delphinium\Iris\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Roots;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Iris\Components\Angular;


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
    
    public function addModule()
    {
        $name = \Input::get('name');
        $unlock_at =\Input::get('unlock_at');
        
        $prerequisite_module_ids =null;//\Input::get('prerequisites');
        $published = \Input::get('published');
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, null);
        
        $req = new ModulesRequest(ActionType::POST, null, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return json_encode($res);
    }
    
    public function addModuleModule()
    {
        
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
}

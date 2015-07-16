<?php namespace Delphinium\Stem\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Roots\Models\Page;
use Delphinium\Roots\Models\Discussion;
use Delphinium\Roots\Models\File;
use Delphinium\Roots\Models\Quiz;
use Delphinium\Roots\Models\Assignment;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\RequestObjects\AssignmentGroupsRequest;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\CommonEnums\ActionType;
use Delphinium\Stem\Classes\ManagerHelper as IrisClass;
use Delphinium\Stem\Components\Manager;
use \DateTime;


class RestfulApi extends Controller 
{
    public function getFreshData()
    {
        $man = new Manager();
        $modules = $man->getModules(true);
        return $modules;
    }
    
    public function getAvailableTags()
    {
        $roots = new Roots();
        return $roots->getAvailableTags();
        
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

        $roots = new Roots();
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

        $iris = new IrisClass();
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
    
    
    public function updateModulePrereqs()
    {
        $module_id = \Input::get('module_id');
        $currentPrereqs = \Input::get('current_prerequisites_ids');
        
        $module = new Module(null, null, $currentPrereqs, null, null);
        
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT, $module_id, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        return $roots->modules($req);
    }
    
    public function updateModuleItemCompletionRequirement()
    {
        $name = \Input::get('name');
        $contentId = \Input::get('id');
        $moduleId = \Input::get('module_id');
        $type = \Input::get('type');
        $url = \Input::get('url');
        $page_url = null;
        $external_url = null;
        switch($type)
        {
            case "Page":
                $page_url = ($url);
                break;
            case "ExternalUrl":
                $external_url = ($url);
                break;
            case "ExternalTool":
                $external_url = ($url);
                break;
        }
        
        //$title = null, $module_item_type=null, $content_id = null, $page_url = null, $external_url = null, 
//        $completion_requirement_type = null, $completion_requirement_min_score = null, $published = false, $position = 1,array $tags = null)
        //TODO: look into completion requirement type
        $moduleItem = new ModuleItem(null, $type, $contentId, $page_url,$external_url, null, 
                null, true, null, null);
                
        $req = new ModulesRequest(ActionType::POST, $moduleId, null,  
            false, false,  null, $moduleItem , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return json_encode($res);
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
        $published = true;
        
        $discussion = new Discussion($title, $published);
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
<?php namespace Delphinium\Raspberry\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Roots;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Raspberry\Classes\Api;


class RestApi extends Controller {
	
    public function index()
    {
        return "Hello, from RestApi";
    }

    public function saveModules()
    {
        $courseId = \Input::get('courseId');
        $modulesArray = \Input::get('modulesArray');

        $decoded = json_decode($modulesArray);
        
        $flat = $this->flatten($decoded, $courseId);

        $roots = new \Delphinium\Core\Roots();
        $mods = $roots->updateModuleOrder($flat);
      
        $iris = new IrisClass();
        $result = $iris->newBuildTree($mods);
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




    public function updateModule()
    {
        $moduleId = \Input::get('moduleId');
        $keyValueParams = json_decode(\Input::get('keyValueParams'), true);
        $api = new Api();
        return $api->updateModule($moduleId, $keyValueParams);
    }

    public function getModuleItems()
    {
        $moduleId = \Input::get('moduleId');
        $courseId = \Input::get('courseId');
        $api = new Api();
        $items = $api->getModuleItems($courseId, $moduleId);
        return json_encode($items);
    }

    public function getTags()
    {
        $contentId = \Input::get('contentId');

        $api = new Api();
        $tags = $api->getTags($contentId);
        return $tags;
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
}
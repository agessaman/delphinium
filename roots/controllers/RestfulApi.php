<?php namespace Delphinium\Roots\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Roots\UpdatableObjects\Module;
use Delphinium\Roots\UpdatableObjects\ModuleItem;
use Delphinium\Roots\RequestObjects\AssignmentsRequest;
use Delphinium\Roots\RequestObjects\ModulesRequest;
use Delphinium\Roots\Roots;
use Delphinium\Roots\Enums\CompletionRequirementType;
use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Models\Page;
use \DateTime;

class RestfulApi extends Controller 
{
    public function getContentByType()
    {
        $roots = new Roots();
        $type = \Input::get('type');
        
        switch($type)
        {
            case "File":
                $response = $roots->getFiles();
                break;
            case "Page":
                $response = $roots->getPages();
                break;
            case "Assignment":
                $req = new AssignmentsRequest(ActionType::GET, null, false);
                $response = $roots->assignments($req);
                $return =array();
                $i=0;
                $assignments = array();
                foreach($response as $item)
                {
                    $file = new \stdClass();

                    $file->id = $item->assignment_id;
                    $file->name=$item->name;
                    $assignments[] = $file;

                    $i++;
                }
                return $assignments;
                
                
            case "Quiz":
                $response = $roots->getQuizzes();
                break;
            case "ExternalTool":
                $response = $roots->getExternalTools();
                break;
            default:
                $response = [];
        }
        
        return json_encode($response);
    }
    
    public function addModule()
    {
        $name = \Input::get('name');
        $date =\Input::get('unlock_at');
        if(!is_null($date))
        {
           $unlock_at= new DateTime($date);
        }
        else
        {
            $unlock_at = null;
        }
        $prerequisite_module_ids =\Input::get('prerequisites');
        $published = \Input::get('published');
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, null);
        
        $req = new ModulesRequest(ActionType::POST, null, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        
//        //add the parent_id;
        $parent_id = \Input::get('parent_id');
        $res->parent_id = $parent_id;
        $roots->updateModuleParent($res);
        return json_encode($res);
    }
    
    public function addModuleItem()
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
        $moduleItem = new ModuleItem($name, $type, $contentId, $page_url,$external_url, null, 
                null, true, null, null);
                
        $req = new ModulesRequest(ActionType::POST, $moduleId, null,  
            false, false,  null, $moduleItem , false);
        
        $roots = new Roots();
        $res = $roots->modules($req);
        return json_encode($res);
    }
    
    
    public function updateModule()
    {
        $name = \Input::get('name');
        $date = \Input::get('unlock_at');
        $unlock_at = new DateTime($date);
        $prerequisite_module_ids =\Input::get('prerequisites');
        $published = \Input::get('published');
        $module_id = \Input::get('module_id');
        
        $module = new Module($name, $unlock_at, $prerequisite_module_ids, $published, null);
        
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT, $module_id, null,  
            false, false, $module, null , false);
        
        $roots = new Roots();
        return $roots->modules($req);
    }
    
    public function updateModuleItem()
    {
//        $name = \Input::get('name');
//        $date = \Input::get('unlock_at');
//        $unlock_at = new DateTime($date);
//        $prerequisite_module_ids =\Input::get('prerequisites');
//        $published = \Input::get('published');
//        $module_id = \Input::get('module_id');
//        
//        
//         $tags = null;//array('New Tag', 'Another New Tag');
//        $title = "New Title from back end";
//        $modItemType = null;// Module type CANNOT be updated
//        $content_id = 2078183;
//        $completion_requirement_min_score = null;//7;
//        $completion_requirement_type = null;//CompletionRequirementType::MUST_SUBMIT;
//        $page_url = null;//"http://www.gmail.com";
//        $published = true;
//        $position = 1;//2;
//        
//        $moduleItem = new ModuleItem($title, $modItemType, $content_id, $page_url, null, $completion_requirement_type, 
//                $completion_requirement_min_score, $published, $position, $tags);
//        //end added
//        
//        $moduleId = 457097;
//        $moduleItemId = 2885671;
//        $includeContentItems = false;
//        $includeContentDetails = false;
//        $module = null;
//        $freshData = false;
//        
//        $req = new ModulesRequest(ActionType::PUT, $moduleId, $moduleItemId,  
//            $includeContentItems, $includeContentDetails, $module, $moduleItem , $freshData);
//        
//        $roots = new Roots();
//        $res = $roots->modules($req);
    
    }
    
}

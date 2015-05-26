<?php namespace Delphinium\Core\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Roots;
use Delphinium\Core\Enums\ModuleItemEnums\CompletionRequirementType;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Models\Page;

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
                $page_url = $url;
                break;
            case "ExternalUrl":
                $external_url = $url;
                break;
            case "ExternalTool":
                $external_url = $url;
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
        echo json_encode($res);
    }
    
    public function addNewModuleItem()
    {
        $title = \Input::get('title');
        $tags = array('Brand', 'New');
        $modItemType = \Input::get('type');
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
    
    public function addPage()
    {
        $title = \Input::get('title');
        $body = \Input::get('body');
        $pageEditingRole = \Input::get('pageEditingRole ');
        $notifyOfUpdate = \Input::get('notifyOfUpdate');
        $published = \Input::get('published');
        $frontPage = \Input::get('frontPage');
        
        $page = new Page($title, $body, $pageEditingRole,  $notifyOfUpdate, $published, $frontPage);
        $roots = new Roots();
        $roots->addPage($page);
    }
}

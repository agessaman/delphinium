<?php namespace Delphinium\Core\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Roots;
use Delphinium\Core\Enums\ModuleItemEnums\CompletionRequirementType;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Iris\Classes\Iris as IrisClass;
use Delphinium\Iris\Components\Angular;


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
//         module_item[page_url]
//String
//Suffix for the linked wiki page (e.g. ‘front-page’). Required for ‘Page’ type.
//
// module_item[external_url]
//String
//External url that the item points to. [Required for ‘ExternalUrl’ and ‘ExternalTool’ types.
//    
        $name = \Input::get('name');
        $contentId = \Input::get('id');
        $moduleId = \Input::get('module_id');
        $type = \Input::get('type');
        $url = \Input::get('url');
        $page_url = null;
        $external_url = null;
        $completion_type = null;
        switch($type)
        {
            case "Assignment":
                $completion_type = CompletionRequirementType::MUST_SUBMIT;
                break;
            case "Discussion":
                $completion_type = CompletionRequirementType::MUST_CONTRIBUTE;
                break;
            case "Page":
                $completion_type = CompletionRequirementType::MUST_CONTRIBUTE;
                $page_url = $url;
                break;
            case "Quiz":
                $completion_type = CompletionRequirementType::MUST_SUBMIT;
                break;
            case "ExternalUrl":
                $external_url = $url;
                break;
            case "ExternalTool":
                $external_url = $url;
                break;
            default:
                $completion_type = null;
        }
        
        //$title = null, $module_item_type=null, $content_id = null, $page_url = null, $external_url = null, 
//        $completion_requirement_type = null, $completion_requirement_min_score = null, $published = false, $position = 1,array $tags = null)
        //TODO: look into completion requirement type
        $moduleItem = new ModuleItem($name, $type, $contentId, $page_url,$external_url, $completion_type, 
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
}

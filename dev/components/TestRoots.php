<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\UpdatableObjects\ModuleItem;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\RequestObjects\AssignmentsRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\ModuleItemEnums\ModuleItemType;
use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\Cache;

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
//        $this->testChangingModuleItem();
//        $this->testUpdatingModule();
//        
//        $this->testDeletingModule();
//                Cache::flush();
//        $this->testBasicModulesRequest();
//        $this->testAddingModule();
//        $this->testAddingModuleItem();
        $this->testingGettingAssignments();
    }
    
    private function testBasicModulesRequest()
    {
        $req = new ModulesRequest(ActionType::GET);
        $req->moduleId = 455418;
        $req->includeContentDetails = true;
        $req->includeContentItems = true;
        $req->moduleItemId = null;//2869243;
        $req->params = null;
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    
    private function testUpdatingModule()
    {   
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT);
        $req->moduleId = 380199;
        $req->moduleItemId = null;
        $params = array("name"=>"New name","published"=>"true");
        $req->params = $params;
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    private function testChangingModuleItem()
    {
        $req = new ModulesRequest(ActionType::PUT);
        $req->moduleId = 380199;
        $req->moduleItemId = 2683431;
        $params = array("title"=>"Subheader","published"=>"true");
        $req->params = $params;
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    
    private function testDeletingModuleItem()
    {
        $req = new ModulesRequest(ActionType::DELETE);
        $req->moduleId = 380199;
        $req->includeContentDetails = true;
        $req->includeContentItems = true;
        $req->moduleItemId = 2683431;
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testDeletingModule()
    {
        $req = new ModulesRequest(ActionType::DELETE);
        $req->moduleId = 455418;
        $req->moduleItemId = 2870946;
        
//        \Cache::flush();
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    private function testAddingModule()
    {
        $name = "Module Coming From API";
        
        $format = 'Y-m-d H:i:s';
        $date = new \DateTime("now");
        $date->add(new \DateInterval('P1D'));
        $unlock_at = $date;
        $prerequisite_module_ids =array("380199","380201");
        
//        $module = new Module($name, $published);
        $module = new Module($name, $unlock_at, $prerequisite_module_ids);
        
        $req = new ModulesRequest(ActionType::POST);
        $req->moduleId = null;
        $req->module = $module;
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testAddingModuleItem()
    {
        $req = new ModulesRequest(ActionType::POST);
        $req->moduleId = 455742;
        
        $title = "Testing module Item";
        $modItemType = ModuleItemType::SUBHEADER;
        
        $page_url = "http://www.google.com";
        $moduleItem = new ModuleItem($title, $modItemType, null, $page_url);
        
        $req = new ModulesRequest(ActionType::POST);
        $req->moduleId = 455742;
        $req->moduleItem = $moduleItem;
        
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
}


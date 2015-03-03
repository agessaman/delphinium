<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Cms\Classes\ComponentBase;

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
        $this->testChangingModuleItem();
//        $this->testUpdatingModule();
//        $this->testBasicModulesRequest();
    }
    
    private function testUpdatingModule()
    {   
        //update a module (changing title and published to false)
        $req = new ModulesRequest(ActionType::PUT);
        $req->moduleId = 380199;
        $req->moduleItemId = null;
        $params = array("name"=>"just changed the title","published"=>"true");
        $req->params = $params;
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    private function testChangingModuleItem()
    {
        $req = new ModulesRequest(ActionType::PUT);
        $req->moduleId = 380199;
        $req->moduleItemId = 2683431;
        $params = array("title"=>"Changed again","published"=>"true");
        $req->params = $params;
        
        $roots = new Roots();
        $res = $roots->modules($req);
    }
    
    
    private function testBasicModulesRequest()
    {
        $req = new ModulesRequest(ActionType::GET);
        $req->moduleId = 380199;
        $req->includeContentDetails = true;
        $req->includeContentItems = true;
        $req->moduleItemId = null;
        
//        \Cache::flush();
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
}


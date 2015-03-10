<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\UpdatableObjects\Module;
use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
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
        $this->testAddingModule();
//        $this->cacheDeal();
        
        
    }
    private function cacheDeal()
    {
        $courseId= 343331;
        $moduleId = 455418;
        $moduleItemId = 2870697;//2869250;
        
        $mItemkey = "{$courseId}-module-{$moduleId}-moduleItem-{$moduleItemId}";
        $moduleKey = "{$courseId}-module-{$moduleId}";

        $moduleItem;
        $modItemId;
        if(Cache::has($mItemkey))
        {
            $moduleItem = Cache::get($mItemkey);
            $modItemId = $moduleItem["module_item_id"];
            if(Cache::has($moduleKey))
            {
                $mdItems = Cache::get($moduleKey)['module_items'];
    //            $mdItems = $module['moduleItems'];
    //            echo count($mdItems);
    //            echo json_encode($mdItems);
    //            
                echo count($mdItems);
                foreach ($mdItems as $key=>$value)
                {
                    echo json_encode($value);
                    if ($value["module_item_id"]===$modItemId) {
                        echo "found in array of items ";
                       unset($mdItems[$key]);
                       $mdItems = array_values($mdItems);
                       break;
                    }
                }
                echo count($mdItems);
                
                //update the module's items
                $moduleItem["module_items"] = $mdItems;
                Cache::forget($moduleKey);
                Cache::forever($moduleKey, $moduleItem);
                echo json_encode($value);
            }
        }
        else
        {
            echo "not in cache";
        }
//return;
        //also delte the module item from the list of module items that belongs to its module parent
        //delete item from DB?
        
//            $foundItem = array_filter($mdItems,
//                function($e) use ($modItemId)
//                {
//                    return $e["id"] === $modItemId;
//                });
//                
//            if(($key = array_search($foundItem, $mdItems)) !== false) {
//                echo "aja, found it";
//                unset($mdItems[$key]);
//                $mdItems = array_values($mdItems);
//            }
//            echo json_encode($foundItem);
            
        
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
        $name = "Adding new module";
        
        $format = 'Y-m-d H:i:s';
        $date = new \DateTime("now");
        $date->add(new \DateInterval('P1D'));
        $unlock_at = $date;
        $published = true;
        $prerequisite_module_ids = "380199,380201";
        
        $module = new Module($name, $unlock_at, $published, $prerequisite_module_ids);
        
        $req = new ModulesRequest(ActionType::POST);
        $req->moduleId = null;
        $req->setModule($module);
        
        
        $roots = new Roots();
        $res = $roots->modules($req);
        echo json_encode($res);
    }
    
    private function testAddingModuleItem()
    {
        $req = new ModulesRequest(ActionType::POST);
        $req->moduleId = 455418;
        $moduleItem = new ModuleItem();
    }
}


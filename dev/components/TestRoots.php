<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
use Delphinium\Core\RequestObjects\ModulesRequest;
use Delphinium\Core\Enums\CommonEnums\ActionType;
use Delphinium\Core\Enums\CommonEnums\Lms;
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
        $req = new ModulesRequest(ActionType::GET, Lms::CANVAS);
        $req->moduleId = 380199;
        $req->includeContentDetails = true;
        $req->includeContentItems = true;
        $req->contentId = null;
        $useCachedData = false;
        $cacheTime = 1;
        $roots = new Roots($useCachedData, $cacheTime);
        $res = $roots->modules($req);
        echo json_encode($res);
    }
}


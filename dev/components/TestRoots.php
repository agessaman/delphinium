<?php namespace Delphinium\Dev\Components;

use Delphinium\Core\Roots;
use Delphinium\Core\RequestObjects\SubmissionsRequest;
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
        $req = new SubmissionsRequest();
//        $req->action = ActionType::GET;   default action is GET
        $req->allStudents = false;
        $req->studentIds = $_SESSION['userID'];
        
        $roots = new Roots();
        $res = $roots->submissions($req);
        echo $res;
    }
}


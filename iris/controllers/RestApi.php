<?php namespace Delphinium\Iris\Controllers;

use Illuminate\Routing\Controller;


class RestApi extends Controller {
    
    public function moveItemToTop()
    {
        $parent = json_decode(\Input::get('parent'), true);
        $threeDArrayWithoutParent = json_decode(\Input::get('modulesArray'), true);
        $iris = new \Delphinium\Iris\Classes\Iris();
        $result = $iris->makeItemParent($threeDArrayWithoutParent,($parent));            
        return $result;
    }
}

<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\RedwoodRoots;

class TestRedwoodRoots extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'TestRedwoodRoots Component',
            'description' => 'Playground for RedwoodRoots'
        ];
    }

    public function onRun()
    {
        $this->test();
    }
    public function test()
    {
        $roots = new RedwoodRoots();
        echo json_encode($roots->getUsers());
    }

}
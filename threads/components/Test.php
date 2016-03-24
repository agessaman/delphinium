<?php namespace Delphinium\Threads\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Threads\Classes\SearchGoogle;

class Test extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Test Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $job = new SearchGoogle('cats');
        $job->start();

// Wait for the job to be finished and print results
        $job->join();
        echo $job->html;
    }

}
<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;


class Leaderboard extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Leaderboard',
            'description' => 'Shows where student sits compaired to others in the class'
        ];
    }

    public function defineProperties()
    {
        return [
            'Instance' => [
                 'title'             => 'Instance',
                'description'       => 'Select the Instance',
                'type'              => 'dropdown',
            ]
        ];
    }

    public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/leaderboard.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/leaderboard.css");
    }

    public function getInstances()
    {
        $instances = Leaderboard::all();
        $array_dropdown = ['0' => 'Select Instance'];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

}
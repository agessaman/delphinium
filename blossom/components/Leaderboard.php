<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class Leaderboard extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'leaderboard',
            'description' => 'Shows where student sits compaired to others in the class'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

        public function onRun()
    {
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/leaderboard.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/leaderboard.css");
    }

}
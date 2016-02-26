<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;

class EasterEggs extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Easter Eggs',
            'description' => 'Find the easter eggs!'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        //$this->addJs("/plugins/delphinium/blossom/assets/javascript/eastereggs.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/harlem-shake.js");
        //$this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
    }

}
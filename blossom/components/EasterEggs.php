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
        $this->addJs("https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/jquery.ripples.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/harlem-shake.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/eastereggs.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/eastereggs.css");
    }

}
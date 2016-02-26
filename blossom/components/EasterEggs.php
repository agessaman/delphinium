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

}
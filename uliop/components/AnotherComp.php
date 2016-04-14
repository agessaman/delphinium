<?php namespace Delphinium\Uliop\Components;

use Cms\Classes\ComponentBase;

class AnotherComp extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'AnotherComp Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}
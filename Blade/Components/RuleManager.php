<?php namespace Delphinium\Blade\Components;

use Cms\Classes\ComponentBase;

class RuleManager extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Rule Manager',
            'description' => 'Set rules that widgets use'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}
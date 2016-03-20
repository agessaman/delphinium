<?php namespace Delphinium\Vanilla\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Vanilla\Models\Vanilla as VanillaModel;

class Vanilla extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Vanilla Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

	public function onRun()
	{
		$config = VanillaModel::all();
		$this->page['config']=$config;
	}
}
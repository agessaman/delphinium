<?php namespace Delphinium\Dev\Components;


use Delphinium\Dev\Models\Configuration;
use Cms\Classes\ComponentBase;

class Dev extends ComponentBase
{
// 	public $chartName;
	
	public function componentDetails()
    {
        return [
            'name'        => 'Dev Component',
            'description' => 'If added to a page it will enable dev mode for Delphinium'
        ];
    }
    
    public function onRun()
    {	
        
        
    }
    
   public function defineProperties()
    {
    	return [
        	'devConfig' => [
            	 'title'             => 'Dev Configuration',
             	'description'       => 'Select the development configuration',
             	'type'              => 'dropdown',
        	]
    	];
    }
    
    public function getDevConfigOptions()
    {
    	$instances = Configuration::where("Enabled","=","1")->get();

        $array_dropdown = ['0'=>'- select dev Config - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Configuration_name;
        }

        return $array_dropdown;
    }
}
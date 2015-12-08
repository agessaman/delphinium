<?php namespace Delphinium\Dev\Components;


use Delphinium\Dev\Models\Configuration;
use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;

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
    	$config = Configuration::find($this->property('devConfig'));
		
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['userID'] = $config->User_id;
        $_SESSION['userToken'] = \Crypt::encrypt($config->Token);
        $_SESSION['courseID'] = $config->Course_id;
        $_SESSION['domain'] = $config->Domain;
        $_SESSION['lms'] = $config->Lms;
        
        //get the timezone
        $roots = new Roots();
        $course = $roots->getCourse();
        $account_id = $course->account_id;
        try
        {   
            $account = $roots->getAccount($account_id);

            $_SESSION['timezone'] = new \DateTimeZone($account->default_time_zone);
        } catch (\GuzzleHttp\Exception\ClientException $e) {

        }
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
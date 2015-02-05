<?php namespace Delphinium\Blackberry\Components;


use \DB;
use Validator;
use October\Rain\Support\ValidationException;
use Delphinium\Blackberry\Models\Developer as LtiConfigurations;
use Delphinium\Blackberry\Models\User;
use Cms\Classes\ComponentBase;
use Delphinium\Blackberry\Classes\Blti;
use Illuminate\Support\Facades\Redirect;

class LTIConfiguration extends ComponentBase
{
	
	public function componentDetails()
    {
        return [
            'name'        => 'LTI Configuration Component',
            'description' => 'Handles the LTI Configuration required for communicating with Canvas'
        ];
    }
    
    public function onRun()
    {
    	//TODO: uncomment line below
    	$this->doBltiHandshake();	
    
    }
    
   public function defineProperties()
	{
    	return [
        	'ltiInstance' => [
            	 'title'             => 'LTI Instance',
             	'description'       => 'Select the LTI configuration instance to use for connecting to Canvas',
             	'type'              => 'dropdown',
        	]
    	];
	}
    
    public function getLtiInstanceOptions()
    {
    	$instances = LtiConfigurations::all();

        $array_dropdown = ['0'=>'- select an LTI configuration - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }
    
    public function doBltiHandshake()
    {
    	//first obtain the details of the LTI configuration they chose, particularly the secret
    	$instanceFromDB = LtiConfigurations::find($this->property('ltiInstance'));
		
	session_start();
        $_SESSION['courseID'] = $_POST['custom_canvas_course_id'];
        $_SESSION['userID'] = $_POST['custom_canvas_user_id'];
        $_SESSION['domain'] = $_POST['custom_canvas_api_domain'];
        
        //Clear database
        //DB::query("DELETE FROM tokens");
        
    	$consumerKey = $instanceFromDB['ConsumerKey'];
		$clientId = $instanceFromDB['DeveloperId'];
        $developerSecret = $instanceFromDB['DeveloperSecret'];
        
//        //save in the session the token and user Id selected
//        $_SESSION['clientID'] = $clientId;
//        $_SESSION['developerSecret'] = $developerSecret;
//        $_SESSION['domain'] = $_POST['custom_canvas_api_domain'];
        
        
    	//Check to see if the lti handshake passes
    	$context = new Blti($consumerKey, false, false);    
       
        if ( $context->valid ) {

		// query DB to see if user has token, if yes, go to LTI.
		$userCheck = User::where('user_id',$_SESSION['userID'])->first();
		
		if (!$userCheck){
			//if not, redirect to canvas permission page
			//TODO: take the domain out into a parameter
			$url = "https://uvu.instructure.com/login/oauth2/auth?client_id=" . $clientId . "&response_type=code&redirect_uri=https://delphinium.uvu.edu/octobercms/saveUserInfo?lti=".$this->property('ltiInstance');
			$this->redirect($url);
		}
                else
                {
                    $_SESSION['userToken'] = $userCheck->encrypted_token;
                    //DON'T REDIRECT, BECAUSE THIS PLUGIN WILL BE USED BY ALL OTHER PLUGINS, AND WE MUST NOT REDIRECT THEM ANYWHERE. 
                    //THIS WILL BE DETERMINED BY THE OTHER PLUGINS
                }
		
	}
	else 
        {
            echo('There is a problem, tell Dr. Chapman');
	}
        
    }
    
	function redirect($url)
	{ 
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>'; exit;
        
	}
    
}

?>
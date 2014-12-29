<?php namespace Delphinium\Blackberry\Components;


use \DB;
use Validator;
use October\Rain\Support\ValidationException;
use Delphinium\Blackberry\Models\Developers as LtiConfigurations;
use Delphinium\Blackberry\Models\Users;
use Cms\Classes\ComponentBase;
use Delphinium\Blackberry\Classes\Blti;

class OAuthResponse extends ComponentBase
{
	
	public function componentDetails()
    {
        return [
            'name'        => 'OAuthResponse',
            'description' => 'Handles the OAuthResponse'
        ];
    }
    
    public function onRun()
    {
    	//TODO: uncomment line below
    	$this->doBltiHandshake();	
    
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
        
    	//Check to see if the lti handshake passes
    	$consumerKey = $instanceFromDB['ConsumerKey'];
        $context = new Blti($consumerKey, false, false);    
    
  
  if ( $context->valid ) {
  
		// query DB to see if user has token, if yes, go to LTI.
		$userCheck = Users::where('UserId',$_SESSION['userID'])->first();
		if (!$userCheck){
			/*if not, redirect to canvas permission page*/
			$clientId = $instanceFromDB['DeveloperId'];
                        //header('Location: https://uvu.instructure.com/login/oauth2/auth?client_id=#########&response_type=code&redirect_uri=https://jchapman.byu.edu/lticanvasui/php/helpers/oauth2response.php
			header("Location: https://uvu.instructure.com/login/oauth2/auth?client_id=" . $clientId . "&response_type=code&redirect_uri=https://delphinium.uvu.edu/octobercms/saveUserInfo");
		}
		else
                {
                    $url = parse_url($_SERVER['HTTP_REFERER']);
                    if(isset($url['query'])){
                            parse_str($url['query'], $query_params);
                            if(isset($query_params['session_started']) and $query_params['session_started'] = "true"){
                                    header('Location: https://delphinium.uvu.edu/octobercms/student-view');	
                            }
                    }else{
                        //TODO: implement this SessionFix thing
                            header('Location: https://jchapman.byu.edu/lticanvasui/php/controllers/sessionFix.php?referingURL=' . $_SERVER['HTTP_REFERER']);
                    }
		}
	}
	else {
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
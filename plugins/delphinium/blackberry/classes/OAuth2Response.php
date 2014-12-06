<?php namespace Delphinium\Blackberry\Classes;
	use Delphinium\Blackberry\Models\User;
	
	class OAuth2Response{
        function __construct($clientID, $clientSecret) {
            session_start();
            
            
            $opts = array('http' => array( 'method'  => 'POST', ));
            $context  = stream_context_create($opts);
            
            //TODO: need to extract this into a parameter
            $url = 'https://uvu.instructure.com/login/oauth2/token?client_id='.$clientID.'&client_secret='.$clientSecret.'&code='.$_GET['code'];
            $userTokenJSON = file_get_contents($url, false, $context, -1, 40000);
            $userToken = json_decode($userTokenJSON);

            //encrypt token
			$encrypted = Crypt::encrypt('$userToken');
            //store encrypted token in the database
                
            $user = new User;
			$user->UserId = $_SESSION['userID'];
			$user->Token=$encryptedToken;
			$user->CourseId = $_SESSION['courseID'];
			$user->save();
			
			//TODO: update this URL. Update saveUserInfo.php using Eloquent ORM
            //$saveUserInfoURL = 'https://jchapman.byu.edu/lticanvasui/php/controllers/saveUserInfo.php?courseID=$courseID';
            //exec( $saveUserInfoURL . " > /dev/null &");

            //  redirect to main tool page /
	        header('Location: https://delphinium.uvu.edu/octobercms/student-view');
	        
	        void session_write_close ( void );
	        
        }
        
	
        
    }
	
?>
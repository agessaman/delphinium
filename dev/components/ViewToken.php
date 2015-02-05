<?php namespace Delphinium\Dev\Components;


use Delphinium\Dev\Models\Configuration;
use Cms\Classes\ComponentBase;

class Dev extends ComponentBase
{
	public function componentDetails()
    {
        return [
            'name'        => 'Token Component',
            'description' => 'Shows the user id and token'
        ];
    }
    
    public function onRun()
    {	
        session_start();
        
        $userId = $_SESSION['userID'];
        $token = $_SESSION['userToken'];
        
        echo "Save your userId and token. You will need it to configure your development environment";
        echo "userId -".$userId;
        echo "token -".$token;
    }

     
}
<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\Models\OAuth as OAuthModel;
use Delphinium\Redwood\Models\Authorization;
use Config;

class Oauth extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'OAuth Component',
            'description' => 'This component will carry out the OAuth process to make REST calls to Process Maker\'s API'
        ];
    }

    public function defineProperties()
    {
        return [
            'configs' => [
                'title'             => 'OAuth Configuration',
                'description'       => 'Select the oauth configuration',
                'type'              => 'dropdown',
            ],
            'workspace'=>[
                'title'             => 'Workspace',
                'description'       => 'Enter the name of the ProcessMaker workspace',
                'type'              => 'string',
            ]
        ];
    }

    public function getConfigsOptions()
    {
        $instances = OAuthModel::get();

        $array_dropdown = ['0'=>'- select configiguration - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->name;
        }

        return $array_dropdown;
    }

    public function onRun()
    {
        //check to see if we have a token for the given workspace. If we don't we'll have to go through the OAuth process
        $authorization = Authorization::where('workspace','=',$this->property('workspace'))->first();

        $baseUrl = Config::get('app.url', 'backend');
        $parts =  parse_url($baseUrl);
        $host = $parts['host'];
        $workspace = $this->property('workspace');
        $pmServer = "http://{$host}:8080";

        if ($authorization)
        {
            $_SESSION['pm_encrypted_access_token'] =$authorization->encrypted_access_token;
            $_SESSION['pm_encrypted_refresh_token'] =  $authorization->encrypted_refresh_token;
            $_SESSION['pm_workspace'] = $this->property('workspace');
            $_SESSION['pm_server'] = $pmServer;
        }
        else
        {
            //STEP 1: Authorize :
            //GET http://{pm-server}/{workspace}/oauth2/authorize?response_type=code&client_id={client-id}&scope={scope}
            //save some variables to a cookie so we can retrieve them after the oauth process is completed. At that point the cookie will be
            //destroyed and we'll save the data we need in the session variables
            setcookie("pm_OAuthCredentialsId",  $this->property('configs'), time() + (86400 * 30), '/');
            setcookie("pm_workspace", $this->property('workspace'), time() + (86400 * 30), '/');

            $credentials = OAuthModel::find($this->property('configs'));
            $client_id = $credentials->client_id;

            //TODO: where do we parameterize these urls?
            $url = "{$pmServer}/oauth2/authorize?response_type=code&client_id={$client_id}&scope=*";
            $this->redirect($url);
        }

    }

    function redirect($url) {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
        exit;
    }
}
<?php namespace Delphinium\Redwood\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Redwood\Models\OAuth as OAuthModel;
use Delphinium\Redwood\Models\Authorization;
use Config;

class OAuth extends ComponentBase
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

    public function onRunOld()
    {
        $config = OAuthModel::find($this->property('configs'));

        $client_id = $config->client_id;
        $client_secret = $config->client_secret;
        $baseUrl = Config::get('app.url', 'backend');
        $parts =  parse_url($baseUrl);

        $host = $parts['host'];
        $redirectUrl =  "{$baseUrl}/redwood/authenticated";

        //TODO: where do we parameterize these urls?
        //authorize url: http://{pm-server}/{workspace}/oauth2/authorize?response_type=code&client_id={client-id}&scope={scope}
        //authorize url: http://localhost/sysworkflow/oauth2/authorize?response_type=code&client_id=XAWXYISSBIVZNWEGAHGRTMZVNTXVLYYF&scope=*
        $authorizeUrl = "http://{$host}/sysworkflow/oauth2/authorize";

        $tokenUrl = "http://{$host}/sysworkflow/oauth2/token";

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $client_id, // The client ID assigned to you by the provider
            'clientSecret'            => $client_secret,   // The client password assigned to you by the provider
            'redirectUri'             =>  $redirectUrl,
            'urlAuthorize'            => $authorizeUrl,
            'urlAccessToken'          => $tokenUrl,
            'urlResourceOwnerDetails' => 'www.google.com'
        ]);


        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            $_SESSION['oauth2state'] = $provider->getState();

            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            try {

                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                echo $accessToken->getToken() . "\n";
                echo $accessToken->getRefreshToken() . "\n";
                echo $accessToken->getExpires() . "\n";
                echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";

                // Using the access token, we may look up details about the
                // resource owner.
                $resourceOwner = $provider->getResourceOwner($accessToken);

                var_export($resourceOwner->toArray());

                // The provider provides a way to get an authenticated API request for
                // the service, using the access token; it returns an object conforming
                // to Psr\Http\Message\RequestInterface.
                $request = $provider->getAuthenticatedRequest(
                    'GET',
                    'http://brentertainment.com/oauth2/lockdin/resource',
                    $accessToken
                );

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token or user details.
                exit($e->getMessage());

            }

        }
    }

    public function onRun()
    {
        //check to see if we have a token for the given workspace. If we don't we'll have to go through the OAuth process
        $authorization = Authorization::where('workspace','=',$this->property('workspace'))->first();

        $baseUrl = Config::get('app.url', 'backend');
        $parts =  parse_url($baseUrl);
        $host = $parts['host'];
        $workspace = $this->property('workspace');
        $pmServer = "http://{$host}:8080/{$workspace}";

        if ($authorization)
        {
            $_SESSION['encrypted_access_token'] =$authorization->encrypted_access_token;
            $_SESSION['encrypted_refresh_token'] =  $authorization->encrypted_refresh_token;
            $_SESSION['workspace'] = $this->property('workspace');
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
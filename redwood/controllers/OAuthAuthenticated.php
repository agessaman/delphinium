<?php namespace Delphinium\Redwood\Controllers;

use Illuminate\Routing\Controller;
use Delphinium\Redwood\Models\OAuth as OAuthModel;
use Delphinium\Redwood\Models\Authorization;
use Config;

/**
 * Restful Api Back-end Controller
 */
class OAuthAuthenticated extends Controller
{
    public function authenticated()
    {
        if(!is_null(\Input::get('code')))
        {
            if(!isset($_COOKIE["pm_workspace"])) {
                echo "An error has occurred. Please contact your instructor";
            } else {

                $workspace =  $_COOKIE["pm_workspace"];
                $credentialsId = $_COOKIE["pm_OAuthCredentialsId"];

                //destroy cookies and set session variables instead
                unset($_COOKIE["pm_workspace"]);
                unset($_COOKIE["pm_OAuthCredentialsId"]);

                if (!isset($_SESSION)) {
                    session_start();
                }

                $baseUrl = Config::get('app.url', 'backend');
                $parts =  parse_url($baseUrl);
                $host = $parts['host'];
                $workspace = "workflow";
                $pmServer = "http://{$host}:8080";

                $_SESSION['pm_workspace'] =$workspace;
                $_SESSION['pm_server'] = $pmServer;
                $credentials = OAuthModel::find($credentialsId);
                //TODO: where do we parameterize these urls

                $postParams = array(
                    'grant_type'    => 'authorization_code',
                    'code'          => \Input::get('code'),
                    'client_id'     => $credentials->client_id,
                    'client_secret' => $credentials->client_secret
                );

                $ch = curl_init($pmServer . "/oauth2/token");
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = json_decode(curl_exec($ch));
                $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpStatus != 200) {
                    print "Error in HTTP status code: $httpStatus\n";
                }
                elseif (isset($result->error)) {
                    print "<pre>Error logging into $pmServer:\n" .
                        "Error:       {$result->error}\n" .
                        "Description: {$result->error_description}\n</pre>";
                }
                else {


                    $encryptedAccessToken = \Crypt::encrypt($result->access_token);
                    $encryptedRefreshToken = \Crypt::encrypt($result->refresh_token);

                    $authorization = Authorization::firstOrNew(array('workspace' => $workspace));
                    $authorization->workspace = $workspace;
                    $authorization->encrypted_access_token = $encryptedAccessToken;
                    $authorization->encrypted_refresh_token = $encryptedRefreshToken;
                    $authorization->expires_in = $result->expires_in;
                    $authorization->token_type = $result->token_type;
                    $authorization->scope = $result->scope;
                    $authorization->save();

                    $_SESSION['pm_encrypted_access_token'] =$encryptedAccessToken;
                    $_SESSION['pm_encrypted_refresh_token'] =  $encryptedRefreshToken;
                }
            }


        }else{
            //check if the user clicked on "Deny" or some other error:
            if (! empty(\Input::get('error'))) {
                print "<pre>";
                print_r($_GET);
                print "</pre>";
                echo "An error occur. Please inform your instructor";
            }
        }

    }
}
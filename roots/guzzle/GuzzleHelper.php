<?php namespace Delphinium\Roots\Guzzle;

use Delphinium\Roots\Enums\CommonEnums\ActionType;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;

class GuzzleHelper
{
    public static function makeRequest($request, $url)
    {
        $client = new Client();
        switch($request->getActionType())
        {
            case ActionType::GET:
                $response = $client->get($url);
                break;
            case ActionType::DELETE:
                $response = $client->delete($url);
                break;
            case ActionType::PUT:
                $response = $client->put($url);
                break;
            case ActionType::POST:
                $response = $client->post($url);
                break;
            default:
                $response = $client->get($url);
        }
        return $response;
    }
    
    public static function getAsset($url)
    {
        $client = new Client();
        return $client->get($url);
    }
    
    public static function postData($url)
    {
        $client = new Client();
        return $client->post($url);
    }
    
    public static function postMultipartRequest($params, $file, $upload_url)
    {
//        $client = new Client();
//        $result = $client->post($upload_url, [
//            'body' => [
//                $params,
//                'file'   => fopen('/Users//Desktop/'.$file, 'r')
//            ]
//        ]);

        echo "here";
        echo json_encode($result);
        return $result;
        //good but it didnt work
//        $client = new Client();
//
//        // Create the request.
//        $request = $client->createRequest("POST", $upload_url);
//
//        // Set the POST information.
//        $postBody = $request->getBody();
//        foreach($params as $key=>$value)
//        {
//            $postBody->setField($key, $value);
//        }
//        $fileName = "/Users//Desktop/".$file;
//        $postBody->addFile(new PostFile('file', fopen($fileName, 'r', 1)));
//
//        echo json_encode($request);
//        // Send the request and get the response.
//        $result = $client->send($request);
//        echo json_encode($result);
//        return $result;
      
    }
            
    public static function constructUrl($urlPieces, $urlArgs = null)
    {
        $urlStr = "";
        for($i = 0;$i<=count($urlPieces)-1;$i++)
        {
//            $urlStr.= $urlPieces[$i]."/";
            if($i===count($urlPieces)-1)
            {
                //we've reached the last url piece. Attach ? for params
                $urlStr.= $urlPieces[$i]."?";//$urlStr.="?";
            }
            else
            {
                $urlStr.= $urlPieces[$i]."/";
            }
        }
        
        if($urlArgs)
        {
            $urlParamsStr = "";
            for($i = 0;$i<=count($urlArgs)-1;$i++)
            {
                $urlParamsStr.= $urlArgs[$i];
                if($i<count($urlArgs)-1)
                {
                    $urlParamsStr.= "&";
                }
            }
        }
     
        $url = $urlStr.$urlParamsStr;
        return $url;
    }
}
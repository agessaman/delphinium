<?php namespace Delphinium\Roots\Guzzle;

use Delphinium\Roots\Enums\ActionType;
use GuzzleHttp\Client;

class GuzzleHelper
{
    public static function makeRequest($request, $url, $getRawResponse = false)
    {

        $client = new Client();
        switch($request->getActionType())
        {
            case ActionType::GET:
                if($getRawResponse)
                {
                    return $client->get($url);
                }
                else
                {
                    $response = GuzzleHelper::getAsset($url);//$client->get($url);
                }
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
                $response = GuzzleHelper::getAsset($url);//$client->get($url);
        }
        return $response;
    }

    public static function recursiveGet($url)
    {
        $data = GuzzleHelper::getAsset($url);
        $currentPage = 1;
        $hasData = true;
        while($hasData)
        {
            $currentPage = $currentPage + 1;
            $newUrl = $url."&page={$currentPage}";
            $next_data = (GuzzleHelper::getAsset($newUrl));
            if(!empty($next_data))
            {
                $data = array_merge($data,$next_data);
            }
            else
            {
                $hasData = false;
            }
        }
        return $data;

    }

    public static function getAsset($url)
    {
        $client = new Client();
//         try {
        $response = $client->get($url);

        $data = json_decode($response->getBody());
        return $data;
        // } catch (\GuzzleHttp\Exception\ClientException $e) {
//             return [];
//         }
    }
    public static function postData($url)
    {
        $client = new Client();
        $response =  $client->post($url);
        return json_decode($response->getBody());
    }

    public static function putData($url, $options)
    {
        $client = new Client();
        $response = $client->put($url,['quiz_submissions' => $options]);
        return json_decode($response->getBody());
    }

    public static function postDataWithParamsCurl($url, $params, $token, $action = 'POST')
    {
//        echo json_encode($params);return;
        $data_string = json_encode($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
                'Authorization: Bearer '.$token
        ));

        $result = curl_exec($ch);
        return $result;
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
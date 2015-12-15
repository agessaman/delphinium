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
        $response = $client->get($url);
        $data = json_decode($response->getBody());
        return $data;
    }
    public static function postData($url)
    {
        $client = new Client();
        $response =  $client->post($url);
        return json_decode($response->getBody());
    }
    
    public static function postDataWithParams($url, $obj)
    {
        $client = new Client();
        $response = $client->post($url,[
              'json' => json_encode($obj)
           ]);
        
        
//        $response = $client->post($url, array(
//            'headers' => array('Content-type' => 'application/json'),
//            'body' => json_encode($paramArray)
//        ));
        
        
        
//        $obj->attempt = $attempt;
//        $obj->validation_token = $quizSubmission->validation_token;
//        $obj->quiz_questions = ($questions);
        
//        this doesn't work
//        $response = $client->request('POST', $url, [
//            'json' => [
//                'attempt' => $paramArray->attempt,
//                'validation_token' => $paramArray->validation_token,
//                'quiz_questions' => [
//                    $paramArray->quiz_questions
//                ]
//            ]
//        ]);
//        echo json_encode($response->getBody());
        
        //this gives 403
//        $response = $client->post($url, ['json' => json_encode($paramArray)]);
        
        
        //this gves 403
//        $request = $client->post($url,($paramArray->toArray()));
//        $request->setBody(json_encode($paramArray)); #set body!
//        $response = $request->send();
//        
//        echo json_encode($request);
//        $json = json_encode($paramArray);
//        $response = $client->request('POST', $url, ['json' => $json]);
        
//        $response =$client->post($url,[
//        'body' => array(
//             json_encode($paramArray)
//        )]);
        return $response;
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
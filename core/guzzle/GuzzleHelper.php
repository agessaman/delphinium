<?php namespace Delphinium\Core\Guzzle;

use Delphinium\Core\Enums\CommonEnums\ActionType;
use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;

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
        $client = new Client();
        $response = $client->post($upload_url, [
            'body' => [
                $params,
                'file' => $file,
                'file_name'   => fopen('/Users/damaris/Desktop/', 'r'),
                'other_file'  => Stream::factory($file)
            ]
        ]);
//        $response = $client->post($url, [
//            'body' => [
//                'field'       => 'abc',
//                'other_field' => '123',
//                'file_name'   => fopen('/path/to/file', 'r'),
//                'other_file'  => Stream::factory('file contents')
//            ]
//        ]);
//        
//        $client->post(
//            '/my/great/url',
//            null,
//            ['file' => '@'.$filename]
//        );
        
//        $response = $client->post($upload_url, [
//            
//            'multipart' => [
//                $params,
//                [
//                    'name'     => 'file',
//                    'contents' => fopen('/Users/damaris/Desktop/'.$file, 'r')
//                ]
//            ]
//        ]);
//        
        return $response;
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
<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Roots\Guzzle;

use Delphinium\Roots\Enums\ActionType;
use Delphinium\Roots\Exceptions\InvalidRequestException;
use GuzzleHttp\Client;

class GuzzleHelper
{
    public static function makeRequest($request, $url, $getRawResponse = false, $token=null)
    {

        //if the raw response is requested, we cannot do the recursive url (for which the token is needed), so we will need to set it to false
        if($getRawResponse==true)
        {
            $token=null;
        }
        $client = new Client();
        switch($request->getActionType())
        {
            case ActionType::GET:
                if($getRawResponse)
                {
                    return $client->get($url);
                }
                else if(!is_null($token))
                {
                    $response = GuzzleHelper::recursiveGet($url, $token);
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

    public static function recursiveGet($url, $token)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); //Requires to load headers
        curl_setopt($ch, CURLOPT_HEADER, 1);  //Requires to load headers
        $result = curl_exec($ch);

        #Parse header information from body response
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        $data = json_decode($body);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($status != 200 and $status != 201)
        {
            $action = "retrieve resource";
            $reason='';
            if ($data and isset($data->errors)) {
                if(count($data->errors)>0)
                {
                    $reason = $data->errors[0]->message;
                }
                else
                {
                    $reason = $data->errors->message;
                }
                throw new \Delphinium\Roots\Exceptions\InvalidRequestException($action, $reason, $status);
            }
        }
        curl_close($ch);
//        #Parse Link Information
        $header_info = GuzzleHelper::http_parse_headers($header);
        if(isset($header_info['Link'])){
            $links = explode(',', $header_info['Link']);
            foreach ($links as $value) {
                if (preg_match('/^\s*<(.*?)>;\s*rel="(.*?)"/', $value, $match)) {
                    $links[$match[2]] = $match[1];
                }
            }
        }
        #Check for Pagination
        if(isset($links['next'])){
            $next_data = GuzzleHelper::recursiveGet($links['next'] . "&access_token=$token", $token);
            $data = array_merge($data,$next_data);
            return $data;
        }else{
            return $data;
        }
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
        curl_close($ch);
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
        $urlParamsStr = "";
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

    private static function http_parse_headers( $header ) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace_callback(
                    '/(?<=^|[\x09\x20\x2D])./',
                    function($m) { return (strtoupper($m[0])); },
                    strtolower(trim($match[1])
                    ));
                if( isset($retVal[$match[1]]) ) {
                    if ( is_array( $retVal[$match[1]] ) ) {
                        $i = count($retVal[$match[1]]);
                        $retVal[$match[1]][$i] = $match[2];
                    }
                    else {
                        $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                    }
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
}
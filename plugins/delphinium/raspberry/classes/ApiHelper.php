<?php namespace Delphinium\Raspberry\Classes;

use Illuminate\Support\Facades\Cache;
use Delphinium\Raspberry\Models\Module;

/*
 * This class is a helper to interact with the Canvas API. 
 */
class ApiHelper{
	
    public function get_api_data($url){
        global $token;
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
        curl_close($ch);

        #Parse Link Information
        $header_info = self::http_parse_headers($header);
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
                $next_data = $this->get_api_data($links['next'] . "&access_token=$token");
                $data = array_merge($data,$next_data);
                return $data;
        }else{
                return $data;
        }
    }	
	
	/*
	 * @url: the url to where the request will be sent
	 * @keyValueParams: a key-value array with the parameters to the post 
	 */
	static function post_api_data($url)
	{
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => $url,
                    //CURLOPT_USERAGENT => 'Codular Sample cURL Request',
                    CURLOPT_PUT=> 1,
                    CURLOPT_VERBOSE=>1,
                    CURLOPT_HEADER=>1
		));
		
		
		// Send the request & save response to $resp
		$result = curl_exec($curl);
                curl_close($curl);
		return $result;
		//return curl_error($curl); 
	}

/*
 * This function helps parse the http headers 
 */
    static function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = ''; // [+]

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                }
                else
                {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            }
            else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                    $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
                elseif (!$key) // [+]
                    $headers[0] = trim($h[0]);trim($h[0]); // [+]
            } // [+]
        }

        return $headers;
    }
}
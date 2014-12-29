<?php
	require_once '../helpers/cryptastic.php';
	require_once '../helpers/meekrodb2.2.class.php';
	DB::$user = '#######';
	DB::$password = '######';
	DB::$dbName = '########';

	//Set variables
	$courseID=$_SESSION['courseID'] ;
	$userID=$_SESSION['userID'];
	$domain=$_SESSION['domain'];


//************************************ Query Tokens ******************************************************************
	//retrieve user token from database
	$encryptedToken = DB::query("SELECT encryptedToken FROM tokens WHERE uid = " . $_SESSION['userID'] );
	
	//decrypt token
	$pass = '#######';
	$salt = '#######';
 	$cryptastic = new cryptastic;
	$key = $cryptastic->pbkdf2($pass, $salt, 1000, 32);
	$token = $cryptastic->decrypt($encryptedToken[0]['encryptedToken'], $key);
	
	//retrieve instructor token from database
	$encryptedTokenInstructor = DB::query("SELECT encryptedToken FROM tokens WHERE uid = 456839" );
	
	//decrypt instructor token
	$pass = '########';
	$salt = '########';
 	$cryptastic = new cryptastic;
	$key = $cryptastic->pbkdf2($pass, $salt, 1000, 32);
	$instructorToken = $cryptastic->decrypt($encryptedTokenInstructor[0]['encryptedToken'], $key);
		
	//********************************** REST Queries ****************************************************************************	
	function get_api_data($url){
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
		$header_info = http_parse_headers($header);
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
			$next_data = get_api_data($links['next'] . "&access_token=$token");
			$data = array_merge($data,$next_data);
			return $data;
		}else{
			return $data;
		}
	}	
?>
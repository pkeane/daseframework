<?php
Class Dase_Http 
{
	function __construct() {}

	public static function put($url,$body,$user,$pass,$mime_type='')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		if ($mime_type) {
			$headers  = array(
				"Content-Type: $mime_type"
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		// returns status code && response body
		return array($info['http_code'],$result,$info);
	}

	public static function delete($url,$user,$pass)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		if ('200' == $info['http_code']) {
			return 'ok';
		} else {
			return $result;
		}
	}

	public static function post($url,$body,$user,$pass,$mime_type='',$useragent='')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		if ($mime_type) {
			$headers  = array(
				"Content-Type: $mime_type"
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if ($useragent) {
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		return array($info['http_code'],$result,$info);
	}

	public static function get($url,$user='',$pass='')
	{
		//todo: error handling
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($user && $pass) {
			curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);  
		// returns status code && response body
		return array($info['http_code'],$result,$info);
	}
}


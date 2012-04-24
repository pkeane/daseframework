<?php

class Dase_EidCookie {

	/** this class deals with the eid cookie and the 
	 * encrypted eid cookie AND provides minimal
	 * generic functionality
	 */

	protected $user_cookiename = 'DASE_USER';
	protected $auth_cookiename = 'DASE_AUTH';
    //this is simply the list of cookies you can set
	protected $cookiemap = array(
		'max' => 'DASE_MAX_ITEMS',
		'display' => 'DASE_DISPLAY_FORMAT',
		'module' => 'DASE_MODULE',
	);
	protected $display_cookiename = 'DASE_DISPLAY_FORMAT';
	protected $app_root;
	protected $module;
	protected $token;

	public function __construct($app_root,$module='',$token)
	{
		$this->app_root = $app_root;
		$this->module = $module;
		$this->token = $token;
	}

	private function getPrefix() 
	{
		//NOTE that the cookie name will be unique per dase instance 
		//(note: HAD been doing it by date, but that's no good when browser & server
		//dates disagree)
		$prefix = str_replace('http://','',$this->app_root);
		$prefix = str_replace('https://','',$this->app_root);
		$prefix = str_replace('.','_',$prefix);
		return str_replace('/','_',$prefix) . '_';
	}

	public function setEid($eid) 
	{
		$pre = $this->getPrefix();
		$key = md5($this->token.$eid);
		setcookie($pre . $this->user_cookiename,$eid,0,'/');
		setcookie($pre . $this->auth_cookiename,$key,0,'/');
	}

	public function set($type,$data) 
	{
		if ('eid' == $type ) {
			$this->setEid($data);
			return;
		}
		$pre = $this->getPrefix();
		if ('module' == $type) {
			$pre = $pre.$this->module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			$cookiename = $pre . $this->cookiemap[$type];
			setcookie($cookiename,$data,0,'/');
		}
	}

	public function get($type,$request_cookies) 
	{
		if ('eid' == $type ) {
			return $this->getEid($request_cookies);
		}
		$pre = $this->getPrefix();
		if ('module' == $type) {
			$pre = $pre.$this->module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			$cookiename = $pre . $this->cookiemap[$type];
			if (isset($request_cookies[$cookiename])) {
				return $request_cookies[$cookiename];
			}
		}
	}

	public function clearByType($type) 
	{
		$pre = $this->getPrefix();
		if ('module' == $type && $this->module) {
			//allows each module their own module cookie
			$pre = $pre.$this->module.'_';
		}
		if (isset($this->cookiemap[$type])) {
			setcookie($pre . $this->cookiemap[$type],"",-86400,'/');
		}
	}

	/** simply checks the cookie */
	public function getEid($request_cookies) 
	{
		$pre = $this->getPrefix();
		$key = '';
		$eid = '';
		if (isset($request_cookies[$pre . $this->user_cookiename])) {
			$eid = $request_cookies[$pre . $this->user_cookiename];
		}
		if (isset($request_cookies[$pre . $this->auth_cookiename])) {
			$key = $request_cookies[$pre . $this->auth_cookiename];
		}
		if ($key && $eid && $key == md5($this->token.$eid)) {
			return $eid;
		}
		return false;
	}

	public function clear() 
	{
		$pre = $this->getPrefix();
		setcookie($pre . $this->user_cookiename,"",-86400,'/');
		setcookie($pre . $this->auth_cookiename,"",-86400,'/');
	}
}



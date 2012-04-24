<?php
Class Dase_User_Exception extends Exception {}

/* the idea here is to enable the dase framework to be used independently of
 * the dase application and so we need a generic user class to instantiates
 * whatever user class the app is supplying 
 */

Class Dase_User 
{
	public $eid;
	public $is_serviceuser;

	function __construct() {}

	public static function get($db,$config)
	{
		$class_name = $config->getAppSettings('user_class'); 

		if (class_exists($class_name)) {
			//allows a DASe instance to be used 
			//as host for modules (no user required)
			if ('Dase_User' == $class_name) {
				return new Dase_User;
			}
			return new $class_name($db);
		} else {
			throw new Dase_User_Exception("Error: $class_name is not a valid class!");
		}
	}

	//must be supplied by db user class:
	public function retrieveByEid($eid)
	{  
		return $this;
	}

	//must be supplied by db user class:
	public function getUserCount()
	{  
		return 1;
	}

	//must be supplied by db user class:
	public function setHttpPassword($token)
	{
		return;
	}

}



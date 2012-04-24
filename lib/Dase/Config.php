<?php

class Dase_Config_Exception extends Exception {}

class Dase_Config {

	private $conf = array();

	public function __construct($base_dir)
	{
		$this->base_dir = $base_dir;
		$this->conf['app'] = array();
		$this->conf['auth'] = array();
		$this->conf['db'] = array();
		$this->conf['request_handler'] = array();
	}

	public function getBasePath()
	{
		return $this->base_dir;
	}

	public function get($key)
	{
		if (isset($this->conf[$key])) {
			return $this->conf[$key];
		} else {
			return false;
		}
	}

	public function getCacheType()
	{
		if (isset($this->conf['cache']['type'])) {
			return $this->conf['cache']['type'];
		} else {
			throw new Dase_Config_Exception('no cache type defined');
		}
	}

	public function getCacheDir()
	{
		$cache_dir = $this->getCache('dir');
		if (!$cache_dir) {
			//default
			return $this->base_dir.'/files/cache';
		}
		if ('/' == substr($cache_dir,0,1)) {
			return $cache_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Config_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$cache_dir;
	}

    public function getLogLevel()
    {
        $log_levels = array(
            'DEBUG' =>  100,
            'INFO' => 200,
            'WARNING' => 300,
            'ERROR' => 400,
        );
        return $log_levels[$this->getAppSettings('log_level')];
    }


	public function getLogDir()
	{
		$log_dir = $this->getAppSettings('log_dir');
		if (!$log_dir) {
			//default
			return $this->base_dir.'/files/log';
		}
		if ('/' == substr($log_dir,0,1)) {
			return $log_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Log_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$log_dir;
	}

	public function getMediaDir()
	{
		$media_dir = $this->getAppSettings('media_dir');
		if (!$media_dir) {
			//default
			return $this->base_dir.'/files/media';
		}
		if ('/' == substr($media_dir,0,1)) {
			return $media_dir;
		}
		if (!$this->base_dir) {
			throw new Dase_Media_Exception('no base_dir defined');
		}
		return $this->base_dir.'/'.$media_dir;
	}

	public function getAppSettings($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['app'][$setting])) {
				return $this->conf['app'][$setting];
			} else {
				return false;
			}
		} else {
			return $this->conf['app'];
		}
	}

	public function getLocalSettings($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['local'][$setting])) {
				return $this->conf['local'][$setting];
			} else {
				return false;
			}
		} else {
			return $this->conf['local'];
		}
	}

	public function getCache($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['cache'][$setting])) {
				return $this->conf['cache'][$setting];
			}
		} else {
			return $this->conf['cache'];
		}
	}

	public function getSearch($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['search'][$setting])) {
				return $this->conf['search'][$setting];
			}
		} else {
			return $this->conf['search'];
		}
	}

	public function getAuth($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['auth'][$setting])) {
				return $this->conf['auth'][$setting];
			}
		} else {
			return $this->conf['auth'];
		}
	}

	public function getDb($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['db'][$setting])) {
				return $this->conf['db'][$setting];
			}
		} else {
			return $this->conf['db'];
		}
	}

	public function getCustomHandlers($setting='') 
	{
		if ($setting) {
			if (isset($this->conf['request_handler'][$setting])) {
				return $this->conf['request_handler'][$setting];
			}
		} else {
			return $this->conf['request_handler'];
		}
	}

	public function getAll()
	{
		return $this->conf;
	}

	public function set($key,$value)
	{
		$this->conf[$key] = $value;
	}

	public function getSecret($key)
	{
		return md5($this->getAuth('token').$key);
	}

	public function getServicePassword($serviceuser)
	{
		return md5($this->getAuth('service_token').$serviceuser);
	}

	public function load($conf_file)
	{
		if ('/' != substr($conf_file,0,1)) {
			if (!$this->base_dir) {
				throw new Dase_Cache_Exception('no base_dir defined');
			}
			$conf_file = $this->base_dir.'/'.$conf_file;
		}
		if (file_exists($conf_file)) {
			$conf = $this->conf;
			include($conf_file);
			$this->conf = $conf;
		}
	}
}

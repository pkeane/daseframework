<?php

class Dase_Cache_File extends Dase_Cache 
{
	private $cache_dir;
	private $filename;
	private $pid; //process id
	private $server_ip;
	private $tempfilename;
	private $ttl;
    protected $log;

	function __construct($config,$ttl=10)
	{
		$this->cache_dir = $config->getCacheDir();
		$this->ttl = $ttl;
		$this->_initDir();
        $this->log = Dase_Logger::instance(LOG_DIR,LOG_LEVEL);
	}

	private function _initDir()
	{
		if (!$this->cache_dir) {
			throw new Dase_Cache_Exception("no cache directory specified");
		}
		if (!file_exists($this->cache_dir)) {
			if (!mkdir($this->cache_dir,0770,true)) {
				throw new Dase_Cache_Exception("cannot create directory: ".$this->cache_dir);
			}
		}
	}

	/** used to expunge search cache for which
	 * we only have an md5
	 */
	public function expungeByHash($md5_hash)
	{
		if ($md5_hash) {
			@unlink($this->cache_dir.'/'. $md5_hash);
		}
	}

	public function expunge() 
	{
		///make sure it is always a cache dir contents we are expunging
		if ('cache' != array_pop(explode('/',$this->cache_dir))) {
			throw new Dase_Cache_Exception("can only expunge contents of directory called 'cache'");
		}
		$i = 0;
		//from PHP Cookbook 2nd. ed p. 718
		$iter = new RecursiveDirectoryIterator($this->cache_dir);
		foreach (new RecursiveIteratorIterator($iter,RecursiveIteratorIterator::CHILD_FIRST) as $file) {
			if (false === strpos($file->getPathname(),'.svn')) {
				if ($file->isDir()) {
					$i++;
					rmdir($file->getPathname());
				} else {
					$i++;
					unlink($file->getPathname());
				}
			}
		}
		return $i;
	}

	public function setCacheDir($cache_dir)
	{
		$this->cache_dir = $cache_dir;
		$this->_initDir();
	}

	public function getCacheDir()
	{
		return $this->cache_dir;
	}

	private function getFilePath($filename,$create_subdir=false) 
	{
		$md5_hash = md5($filename);
		$subdir = substr($md5_hash,0,2);
		if ($create_subdir && !file_exists($this->cache_dir.'/'.$subdir)) {
			mkdir($this->cache_dir.'/'.$subdir);
			chmod($this->cache_dir.'/'.$subdir,0770);
		}
		return $this->cache_dir.'/'.$subdir.'/'.$md5_hash;
	}

	public function expire($filename)
	{
		@unlink($this->getFilePath($filename));
	}

	/** any data fetch can override the default ttl */
	public function getData($filename,$ttl=0)
	{
		$filepath = $this->getFilePath($filename);
		if (!file_exists($filepath)) {
			return false;
		}

		$time_to_live = $ttl ? $ttl : $this->ttl;
		$stat = @stat($filepath);
		if(time() > $stat[9] + $time_to_live) {
			//delete out of date files
			//print time()."    ";
			//print $stat[9];exit;
			//print $time_to_live;exit;
			@unlink($filepath);
			return false;
		}
		$this->log->logDebug('cache HIT!!! '.$filepath);
		return file_get_contents($filepath);
	}

	public function setData($filename,$data)
	{ 
		$filepath = $this->getFilePath($filename,true);
		$temp = $filepath.'-temp';
		//avoids race condition
		if ($data) {
			@file_put_contents($temp,$data);
			@rename($temp,$filepath);
			@chmod($filepath,0770);
		}
		return $filepath;
	}
}


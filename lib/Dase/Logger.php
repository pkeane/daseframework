<?php
class Dase_Logger_Exception extends Exception {}

class Dase_Logger 
{
	private $filehandle;
	private $log_file;
	private static $instance;

	const OFF 		= 0;	// Nothing at all.
	const INFO 		= 1;	// Production 
	const DEBUG 	= 2;	
	const ERROR 	= 3;	// Most Verbose

	public function __construct($log_dir,$log_level) 
	{
			$this->log_dir = $log_dir;
			$this->log_level = $log_level;
			$this->log_file = trim($this->log_dir).'/log_'.date('Y-m-d').'.txt';
	}

	public function __destruct()
	{
		if ($this->filehandle) {
			fclose($this->filehandle);
		}
	}

	public static function instance($log_dir,$log_level) 
	{
		if (empty( self::$instance )) {
			self::$instance = new Dase_Logger($log_dir,$log_level);
		}
		return self::$instance;
	}

	public function logInfo($msg) 
	{
			if ($this->log_level < Dase_Logger::INFO) {
					return;
			}
			$this->_write($msg,'INFO');
	}

	public function logDebug($msg) 
	{
			if ($this->log_level < Dase_Logger::DEBUG) {
					return;
			}
			$this->_write($msg,'DEBUG');
	}

	public function logError($msg) 
	{
			if ($this->log_level < Dase_Logger::ERROR) {
					return;
			}
			$this->_write($msg,'ERROR');
	}

	private function _init()
	{
		if (!$this->log_file) { 
			return false;
		}

		$filehandle = @fopen($this->log_file, 'a');

		if (!is_resource($filehandle)) {
			return false;
		}

		$this->filehandle = $filehandle;
		return true;
	}

	private function _write($msg,$level)
	{
		if (!$this->_init()) {
			return false;
		}
		$date = date(DATE_ATOM);
		$msg = $date.' ['.$level.'] pid: '.getmypid().' : '.$msg."\n";
		if (fwrite($this->filehandle, $msg) === FALSE) {
			throw new Dase_Logger_Exception('cannot write to log_file '.$this->log_file);
		}
		return true;
	}

}

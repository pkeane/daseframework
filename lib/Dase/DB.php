<?php

class Dase_DB {

	private $type;
	private $path;
	private $host;
	private $name;
	private $user;
	private $pass;
	public $table_prefix;
	public $dbh;
	public $config;
    public $log;

	public function __construct($config)
	{ 
		$this->type = $config->getDb('type');
		$this->host = $config->getDb('host');
		$this->name = $config->getDb('name');
		$this->user = $config->getDb('user');
		$this->pass = $config->getDb('pass');
		if ($config->getDb('path')) {
			$this->path = $config->getDb('path');
		}
		$this->table_prefix = $config->getDb('table_prefix');
		//also store copy of config object
		$this->config = $config;
	}

	public function getDbh()
	{
		if ($this->dbh) {
			return $this->dbh;
		}
		$driverOpts = array();
		if ('sqlite' == $this->type) {
			$dsn = "sqlite:".$this->path;
		} else {
			$dsn = $this->type . ":host=".$this->host.";dbname=".$this->name;
		}
		try {
			$this->dbh = new PDO($dsn, $this->user, $this->pass, $driverOpts);
			if ('mysql' == $this->type) {
				$this->dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
				//http://netevil.org/blog/2006/apr/using-pdo-mysql
				$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			}
		} catch (PDOException $e) {
			throw new  PDOException('connect failed: ' . $e->getMessage());
		}
		return $this->dbh;
	}

	/** simply wraps logging */
	public function query($sql)
	{
		$dbh = $this->getDbh();
		return $dbh->query($sql);
	}

	public function getDbName()
	{
		return $this->name;
	}

	public function setDbName($name)
	{
		$this->name = $name;
		return $this->name;
	}

	public function getDbType()
	{
		return $this->type;
	}

	public function getCaseInsensitiveLikeOp()
	{
		if ('pgsql' == $this->type) {
			return 'ILIKE';
		} else {
			return 'LIKE';
		}
	}

	public function listTables()
	{
		//try-catch-throw since we use this during install
		try {
			$dbh = $this->getDbh();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
		if ('mysql' == $this->type) {
			$sql = "SHOW TABLES";
		}
		//from Zend Db Adapter
		if ('pgsql' == $this->type) {
			$sql = "SELECT c.relname AS table_name "
				. "FROM pg_class c, pg_user u "
				. "WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
				. "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
				. "AND c.relname !~ '^(pg_|sql_)' "
				. "UNION "
				. "SELECT c.relname AS table_name "
				. "FROM pg_class c "
				. "WHERE c.relkind = 'r' "
				. "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
				. "AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
				. "AND c.relname !~ '^pg_'";
		}
		if ('sqlite' == $this->type) {
			$sql = "
				SELECT name FROM sqlite_master
				WHERE type='table'
				ORDER BY name
				";
		}
		$sth = $dbh->prepare($sql);
		if (!$sth) {
			$errs = $dbh->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception('could not create handle: '.$errs[2]);
			}
		}
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public function listColumns($table)
	{
		$dbh = $this->getDbh();
		if ('mysql' == $this->type) {
			$sql = "SHOW FIELDS FROM $table";
		}
		if ('pgsql' == $this->type) {
			$sql = "SELECT attname FROM pg_class, pg_attribute WHERE 
				pg_class.relname = '$table' AND pg_class.oid = pg_attribute.attrelid AND 
				pg_attribute.attnum > 0  
				AND attname NOT LIKE '....%'
				ORDER BY attname";
		}
		if ('sqlite' == $this->type) {
			$sql = "PRAGMA table_info($table)";
			$sth = $this->dbh->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$names[] = $row['name'];
				//$type = $row['type'];
			}
			return $names;
		}
		$sth = $dbh->prepare($sql);
		if (!$sth) {
			$errs = $dbh->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception('could not create handle: '.$errs[2]);
			}
		}
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public function getMetadata($table)
	{
		$dbh = $this->getDbh();
		if ('sqlite' == $this->type) {
			$sql = "PRAGMA table_info($table)";
			$sth = $dbh->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$col = array();
				$col['column_name'] = $row['name'];
				if (strpos($row['type'],'(')) {
					$col['data_type'] = substr($row['type'],0,strpos($row['type'],'('));
					if ('varchar' == $col['data_type']) {
						preg_match('/\d+/',$row['type'],$matches);
						$col['character_maximum_length'] = $matches[0];
					}
				} else{
					$col['data_type'] = $row['type'];
				}
				$result[] = $col;
			}
			return $result;
		}
		$sql = "SELECT column_name, data_type, character_maximum_length, is_nullable,column_default
			FROM information_schema.columns 
			WHERE table_name = '$table'
			AND table_schema = '$this->name'
			";
		$sth = $dbh->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_ASSOC));
	}	

}

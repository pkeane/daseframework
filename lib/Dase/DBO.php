<?php
require_once 'Dase/DB.php';

class Dase_DBO_Exception extends Exception {}

class Dase_DBO implements IteratorAggregate
{
	protected $fields = array(); 
	private $table;
	protected $limit;
	protected $order_by;
	protected $qualifiers = array();
    protected $log;
	public $bind = array();
	public $config;
	public $db;
	public $id = 0;
	public $sql;

	function __construct($db, $table, $fields )
	{
		$this->db = $db;
		//so any DBO has a copy of config
		$this->config = $db->config;
		$this->table = $db->table_prefix.$table;
		foreach( $fields as $key ) {
			$this->fields[ $key ] = null;
		}
        $this->log = Dase_Logger::instance(LOG_DIR,LOG_LEVEL);
	}

	public function getTable($include_prefix = true)
	{
		if ($include_prefix) {
		return $this->table;
		} else {
			$prefix = $this->db->table_prefix;
			return substr_replace($this->table,'',0,strlen($prefix));
		}
	}

	public function __get( $key )
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			return $this->fields[ $key ];
		}
		//automatically call accessor method is it exists
		$classname = get_class($this);
		$method = 'get'.ucfirst($key);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		}	
	}

	public function __set( $key, $value )
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			$this->fields[ $key ] = $value;
			return true;
		}
		return false;
	}

	private function _dbGet() {
		try {
			return $this->db->getDbh();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	function getFieldNames() {
		return array_keys($this->fields);
	}

	function hasMember($key)
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			return true;
		} else {
			return false;
		}
	}

	function setLimit($limit)
	{
		$this->limit = $limit;
	}

	function orderBy($ob)
	{
		$this->order_by = $ob;
	}

	function addWhere($field,$value,$operator)
	{
		if ( 
			array_key_exists( $field, $this->fields) &&
			in_array(strtolower($operator),array('is not','is','ilike','like','not ilike','not like','=','!=','<','>','<=','>='))
		) {
			$this->qualifiers[] = array(
				'field' => $field,
				'value' => $value,
				'operator' => $operator
			);
		} else {
			throw new Dase_DBO_Exception('addWhere problem');
		}
	}

	function __toString()
	{
		$members = '';
		$table = $this->table;
		$id = $this->id;
		foreach ($this->fields as $key => $value) {
			$members .= "$key: $value\n";
		}
		$out = "--$table ($id)--\n$members\n";
		return $out;
	}

	function load( $id )
	{
		if (!$id) {return false;}
		$this->id = $id;
		$db = $this->_dbGet();
		$table = $this->table;
		$sql = "SELECT * FROM $table WHERE id=:id";
		$sth = $db->prepare($sql);
		if (! $sth) {
			$errs = $db->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception($errs[2]);
			}
		}
		$this->log->logDebug($sql . ' /// '.$id);
		$sth->setFetchMode(PDO::FETCH_INTO, $this);
		$sth->execute(array( ':id' => $this->id));
		if ($sth->fetch()) {
			return $this;
		} else {
			return false;
		}
	}

	function insert()
	{ //postgres needs id specified
		if ('pgsql' == $this->db->getDbType()) {
            $seq = $this->table . '_id_seq';
			$id = "nextval(('public.$seq'::text)::regclass)"; 	
		} elseif ('sqlite' == $this->db->getDbType()) {
			$id = 'null';
		} else {
			$id = 0;
		}
		$dbh = $this->db->getDbh();
		$fields = array('id');
		$inserts = array($id);
		foreach( array_keys( $this->fields ) as $field )
		{
			$fields []= $field;
			$inserts []= ":$field";
			$bind[":$field"] = $this->fields[ $field ];
		}
		$field_set = join( ", ", $fields );
		$insert = join( ", ", $inserts );
		//$this->table string is NOT tainted
		$sql = "INSERT INTO ".$this->table. 
			" ( $field_set ) VALUES ( $insert )";
		$sth = $dbh->prepare( $sql );
		if (! $sth) {
			$error = $db->errorInfo();
			throw new Exception("problem on insert: " . $error[2]);
			exit;
		}
		if ($sth->execute($bind)) {
			$last_id = $dbh->lastInsertId($seq);
			$this->id = $last_id;
			$this->log->logDebug($sql." /// last insert id = $last_id");
			return $last_id;
		} else { 
			$error = $sth->errorInfo();
			throw new Exception("could not insert: " . $error[2]);
		}
	}

	function getMethods()
	{
		$class = new ReflectionClass(get_class($this));
		return $class->getMethods();
	}

	function findOne()
	{
		$this->setLimit(1);
		$set = $this->find()->fetchAll();
		if (count($set)) {
			return $set[0];
		}
		return false;
	}

	function findAll($return_empty_array=false)
	{
		$set = array();
		$iter = $this->find();
		foreach ($iter as $it) {
			$set[$it->id] = clone($it);
		}
		if (count($set)) {
			return $set;
		} else {
			if ($return_empty_array) {
				return $set;
			}
			return false;
		}
	}

	function find()
	{
		//finds matches based on set fields (omitting 'id')
		//returns an iterator
		$dbh = $this->db->getDbh();
		$sets = array();
		$bind = array();
		$limit = '';
		foreach( array_keys( $this->fields ) as $field ) {
			if (isset($this->fields[ $field ]) 
				&& ('id' != $field)) {
					$sets []= "$field = :$field";
					$bind[":$field"] = $this->fields[ $field ];
				}
		}
		if (isset($this->qualifiers)) {
			//work on this
			foreach ($this->qualifiers as $qual) {
				$f = $qual['field'];
				$op = $qual['operator'];
				//allows is to add 'is null' qualifier
				if ('null' == $qual['value']) {
					$v = $qual['value'];
				} else {
					$v = $dbh->quote($qual['value']);
				}
				$sets[] = "$f $op $v";
			}
		}
		$where = join( " AND ", $sets );
		if ($where) {
			$sql = "SELECT * FROM ".$this->table. " WHERE ".$where;
		} else {
			$sql = "SELECT * FROM ".$this->table;
		}
		if (isset($this->order_by)) {
			$sql .= " ORDER BY $this->order_by";
		}
		if (isset($this->limit)) {
			$sql .= " LIMIT $this->limit";
		}
		$sth = $dbh->prepare( $sql );
		if (!$sth) {
			throw new PDOException('cannot create statement handle');
		}

		//pretty logging
		$log_sql = $sql;
		foreach ($bind as $k => $v) {
			$log_sql = preg_replace("/$k/","'$v'",$log_sql,1);
		}
		$this->log->logDebug('[DBO find] '.$log_sql);

		$sth->setFetchMode(PDO::FETCH_INTO,$this);
		$sth->execute($bind);
		//NOTE: PDOStatement implements Traversable. 
		//That means you can use it in foreach loops 
		//to iterate over rows:
		// foreach ($thing->find() as $one) {
		//     print_r($one);
		// }
		return $sth;
	}

	function findCount()
	{
		$dbh = $this->db->getDbh();
		$sets = array();
		$bind = array();
		foreach( array_keys( $this->fields ) as $field ) {
			if (isset($this->fields[ $field ]) 
				&& ('id' != $field)) {
					$sets []= "$field = :$field";
					$bind[":$field"] = $this->fields[ $field ];
				}
		}
		if (isset($this->qualifiers)) {
			//work on this
			foreach ($this->qualifiers as $qual) {
				$f = $qual['field'];
				$op = $qual['operator'];
				//allows is to add 'is null' qualifier
				if ('null' == $qual['value']) {
					$v = $qual['value'];
				} else {
					$v = $dbh->quote($qual['value']);
				}
				$sets[] = "$f $op $v";
			}
		}
		$where = join( " AND ", $sets );
		if ($where) {
			$sql = "SELECT count(*) FROM ".$this->table. " WHERE ".$where;
		} else {
			$sql = "SELECT count(*) FROM ".$this->table;
		}
		$sth = $dbh->prepare( $sql );
		if (!$sth) {
			throw new PDOException('cannot create statement handle');
		}
		$log_sql = $sql;
		foreach ($bind as $k => $v) {
			$log_sql = preg_replace("/$k/","'$v'",$log_sql,1);
		}
		$this->log->logDebug('[DBO findCount] '.$log_sql);
		$sth->execute($bind);
		//$this->log->logDebug('DB ERROR: '.print_r($sth->errorInfo(),true));
		return $sth->fetchColumn();
	}

	function update()
	{
		$dbh = $this->db->getDbh();
		foreach( $this->fields as $key => $val) {
			if ('timestamp' != $key || !is_null($val)) { //prevents null timestamp as update
				$fields[]= $key." = ?";
				$values[]= $val;
			}
		}
		$set = join( ",", $fields );
		$sql = "UPDATE {$this->{'table'}} SET $set WHERE id=?";
		$values[] = $this->id;
		$sth = $dbh->prepare( $sql );
		$this->log->logDebug($sql . ' /// ' . join(',',$values));
		if (!$sth->execute($values)) {
			$errs = $sth->errorInfo();
			if (isset($errs[2])) {
				$this->log->logDebug("updating error: ".$errs[2]);
				//throw new Dase_DBO_Exception('could not update '. $errs[2]);
			}
		} else {
			return true;
		}
	}

	function delete()
	{
		$dbh = $this->db->getDbh();
		$sth = $dbh->prepare(
			'DELETE FROM '.$this->table.' WHERE id=:id'
		);
		$this->log->logDebug("deleting id $this->id from $this->table table");
		return $sth->execute(array( ':id' => $this->id));
		//probably need to destroy $this here
	}

	//implement SPL IteratorAggregate:
	//now simply use 'foreach' to iterate 
	//over object properties
	public function getIterator()
	{
		return new ArrayObject($this->fields);
	}

	public function asArray()
	{
		foreach ($this->fields as $k => $v) {
			$my_array[$k] = $v;
		}
		return $my_array;
	}

	public function asJson()
	{
		return Dase_Json::get($this->asArray());
	}
}

<?php

require_once 'Dase/DBO/Autogen/User.php';

class Dase_DBO_User extends Dase_DBO_Autogen_User 
{
	public $is_superuser;
    public $data_array = array();

	public static function get($db,$id)
	{
		$user = new Dase_DBO_User($db);
		$user->load($id);
		return $user;
	}

    public function initData()
    {
        if ($this->data) {
            $this->data_array = json_decode($this->data,1);
        }
    }

    public function addHistoryTitle($title)
    {
        $this->initData();
        if (!isset($this->data_array['history'])) {
            return;
        }
        reset($this->data_array['history']);
        $first_key = key($this->data_array['history']);
        $this->data_array['history'][$first_key] = $title;
        $this->update();
    }

    public function addHistory($uri)
    {
        $this->initData();
        if (!isset($this->data_array['history'])) {
            $this->data_array['history'] = array();
        }
        //remove serial number if it is in history
        if (in_array($uri,$this->data_array['history'])) {
            $index = array_search($uri,$this->data_array['history']);
            unset($this->data_array['history'][$index]);
        }
        if (count($this->data_array['history']) > 7) {
            array_pop($this->data_array['history']);
        }

        $new = array($uri => $uri);
        $this->data_array['history'] = $new + $this->data_array['history'];
        $this->update();
    }

    public function insert($seq='') {
        $this->data = json_encode($this->data_array);
        parent::insert();
    }

    public function update() {
        $this->data = json_encode($this->data_array);
        parent::update();
    }

	public function getUserCount()
	{
		$user = new Dase_DBO_User($this->db);
		return $user->findCount();
	}

	public function retrieveByEid($eid)
	{
		$prefix = $this->db->table_prefix;
		$dbh = $this->db->getDbh(); 
		$sql = "
			SELECT * FROM {$prefix}user 
			WHERE lower(eid) = ?
			";	
		$sth = $dbh->prepare($sql);
		$sth->execute(array(strtolower($eid)));
		$row = $sth->fetch();
		if ($row) {
			foreach ($row as $key => $val) {
				$this->$key = $val;
			}
			$this->log->logDebug('DEBUG: retrieved user '.$eid);
			return $this;
		} else {
			$this->log->logDebug('DEBUG: could NOT retrieve user '.$eid);
			return false;
		}
	}

	public function setHttpPassword($token)
	{
		$this->http_password = substr(md5($token.$this->eid.'httpbasic'),0,12);
		return $this->http_password;
	}

	public function getHttpPassword($token=null)
	{
		if (!$token) {
			if ($this->http_password) {
				//would have been set by request
				return $this->http_password;
			}
			throw new Dase_Exception('user auth is not set');
		}
		if (!$this->http_password) {
			$this->http_password = $this->setHttpPassword($token);
		}
		return $this->http_password;
	}

	public function isSuperuser($superusers)
	{
		if (in_array($this->eid,array_keys($superusers))) {
			$this->is_superuser = true;
			return true;
		}
		return false;
	}
}

<?php

class Dase_ModuleHandler_Dbadmin extends Dase_Handler {

	public $resource_map = array(
		'/' => 'info',
		'index' => 'info',
		'index/{msg}' => 'info',
	);

	public function setup($r)
	{
	}

	public function getInfo($r) 
	{
		$types['sqlite'] = "SQLite";
		$types['mysql'] = "MySQL";
		$types['pgsql'] = "PostgreSQL";

		foreach ($this->db->listTables() as $t) {
			$tables[$t][] = 'id';
			foreach ($this->db->listColumns($t) as $c) {
				if ('id' != $c) {
					$tables[$t][] = $c;
				}
			}
		}
		$r->assign('tables',$tables);
		$r->assign('db',$types[$this->db->getDbType()]);
		$r->renderTemplate('index.tpl');
	}
}
